<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

//引用自定义函数
include_once "../function.php";

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

require_once '../../vendor/autoload.php';
require_once('../../myclass/Excel.php');

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Paragraph;

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$excel = new \myclass\Excel();

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
$path = count($data->path) == 0 ? '' : object_array($data->path[0]); //文章信息压缩包

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');
//判断后台用户权限
if (!my_power("query_add")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$fileZip = "../.." . $path["url"]; //压缩包文件名
$fileExcel = "list.xlsx"; //数据Excel表

if (file_exists($fileZip)) {
    $excelData = $excel::readExcelFromZip($fileZip, $fileExcel); //excel表格信息

    $insert_success_id = array(); //插入成功的ID
    $insert_error_num = array(); //插入失败的Excel编号
    $field_title_xlsx = array(); //获取list.xlsx表格中有哪些自定义字段
    $field_custom = getFieldCustom(); //获取产品自定义信息字段
    if (count($excelData) > 2) {
        //获取自定义字段信息
        foreach ($excelData as $key => $value) {
            if ($key === 1) {
                foreach ($value as $k => $val) {
                    if ($k > 4) {
                        $field_title_xlsx[] = $val;
                    }
                }
            }
        }
        //整理存入数据库的数据
        foreach ($excelData as $key => $value) {
            if ($key > 1) {
                $number = $value[0]; //Excel表中的编号
                $q_condition = $value[1];
                $t_info = empty($value[2]) ? array() : getThumbnail($value[2]); //获取缩略图数组
                $thumbnail = count($t_info) == 0 ? '' : json_encode($t_info, JSON_UNESCAPED_UNICODE); //缩略图
                $content = $value[3]; //查询详情
                $state = empty($value[4]) || ($value[4] != '1' && $value[4] != '2') ? '' : $value[4]; //状态
                $add_time = time(); //添加时间
                $field = [];

                if (count($field_custom) > 0) { //当存在自定义字段时，拼凑自定义字段信息
                    $filed_info = []; //字段信息
                    if (count($field_title_xlsx) > 0) {
                        foreach ($field_title_xlsx as $k => $val) {
                            $filed_info[$val] = $value[5 + $k];
                        }

                        foreach ($field_custom as $val) {
                            $field_type = (int)$val["field_type"]; //字段类型
                            $field_title = $val["field_title"]; //字段名称
                            if ($field_type === 3) {
                                $field_content = json_encode(explode(',', $filed_info[$field_title]), JSON_UNESCAPED_UNICODE);
                            } elseif ($field_type === 4) {
                                $field_content = getContent($filed_info[$field_title]);
                            } elseif ($field_type === 5) {
                                $f_t_info = empty($filed_info[$field_title]) ? array() : getThumbnail($filed_info[$field_title]); //获取缩略图数组
                                $field_content = count($f_t_info) == 0 ? '' : json_encode($f_t_info, JSON_UNESCAPED_UNICODE); //单图上传
                            } elseif ($field_type === 6) {
                                $f_p_info = empty($filed_info[$field_title]) ? array() : getPhotoAlbum($filed_info[$field_title]); //获取多图上传数组
                                $field_content = count($f_p_info) == 0 ? '' : json_encode($f_p_info, JSON_UNESCAPED_UNICODE); //多图上传
                            } elseif ($field_type === 7) {
                                $f_e_info = empty($filed_info[$field_title]) ? array() : getEnclosure($filed_info[$field_title]); //获取附件上传数组
                                $field_content = count($f_e_info) == 0 ? '' : json_encode($f_e_info, JSON_UNESCAPED_UNICODE); //附件上传
                            } else {
                                $field_content = $filed_info[$field_title];
                            }
                            $val["field_content"] = $field_content;
                            $field[] = $val;
                        }
                    }
                }

                $sql = "insert into query (q_condition,thumbnail,content,state,add_time) values (?,?,?,?,?)";
                $stmt = $link->prepare($sql);
                $stmt->bind_param("sssss", $q_condition, $thumbnail, $content, $state, $add_time);
                if ($stmt->execute()) {
                    $record_id = mysqli_insert_id($link); //当前新增记录的ID
                    $insert_success_id[] = $record_id;
                    //向字段信息表里面存储数据
                    if (count($field) > 0) {
                        $field_values = "";
                        foreach ($field as $k => $val) {
                            $field_values .= "(";
                            $field_values .= "'{$val["table_name"]}'"; //表名
                            $field_values .= ",";
                            $field_values .= $record_id;  //产品表对应的记录ID
                            $field_values .= ",";
                            $field_values .= "'{$val["field_name"]}'"; //字段名
                            $field_values .= ",";
                            $field_values .= "'{$val["field_content"]}'"; //字段内容
                            $field_values .= ",";
                            $field_values .= "'{$add_time}'"; //添加时间
                            $field_values .= ")";
                            $field_values .= $k == count($field) - 1 ? "" : ",";
                        }
                        $sql_field_info = "insert into field_info (table_name, record_id, field_name, field_content, add_time) values {$field_values}";
                        $res_field_info = my_sql($sql_field_info);
                        if (!$res_field_info) {
                            $code = 100;  //响应码
                            $message = '出错了，请联系管理员！';  //响应信息
                        }
                    }
                } else {
                    $insert_error_num[] = $number;
                    $obj["data"]["error"][] = "Error: " . $sql . "，信息：" . $link->error;
                }
            }
        }

        if (count($insert_success_id) > 0) {
            $code = 200; //响应码
            $message = 'success'; //响应信息
            $total = count($excelData) - 2; //总共需要插入的记录条数
            $obj["data"]["success"] = '共有' . $total . '条数据需要插入，成功' . count($insert_success_id) . '条，失败' . count($insert_error_num) . '条';
            $obj["data"]["success_num"] = count($insert_success_id);
            $obj["data"]["error_num"] = $insert_error_num;
            $updateTips = "查询管理，通过zip压缩包批量添加查询信息，ID：" . implode(',', $insert_success_id); //操作日志事件内容
            updatelogs($updateTips); //记录操作日志
        }
    } else {
        $code = 100; //响应码
        $message = '文件数据为空，请检查后重新上传！'; //响应信息
    }
} else {
    $code = 100; //响应码
    $message = '文件不存在，请检查后重新上传'; //响应信息
}
//关闭数据库链接
mysqli_close($link);

if (file_exists($fileZip)) {
    unlink($fileZip); //删除压缩包
}

//获取自定义字段信息
function getFieldCustom(): array
{
    $field_custom = [];
    //sql查询语句
    $sql = "select * from field_custom where table_name='query'";
    $res = my_sql($sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row["field_default_value"] = json_decode($row["field_default_value"]);
            $field_custom[] = $row;
        }
    }
    return $field_custom;
}

//获取缩略图
function getThumbnail($proTitle): array
{
    global $fileZip; // 压缩包文件
    $fileThumbnail = "thumbnail/"; // 指定想要获取图片的文件夹路径（ZIP包内的路径）
    $move_to = '../../media_library/'; // 图片要转移的目录
    $pic_info = array(); //返回新的图片信息

    $zip = new ZipArchive();
    if ($zip->open($fileZip) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name']; //获取文件名称

            // 检查文件是否为图片（通过检查文件签名）
            if (strpos($filename, $fileThumbnail) === 0 && preg_match('/\.(jpg|jpeg|png|gif)$/i', $filename)) {
                // 获取图片内容
                $imageData = $zip->getFromName($filename);

                // 获取原始文件名和文件扩展名
                $parts = pathinfo($filename);
                if ($parts['filename'] === $proTitle) {
                    // 新的文件名（这里简单使用了索引，你可以根据自己的需求来定制命名规则）
                    $newFilename = 'image_' . date('YmdHis') . chr(mt_rand(65, 90)) . '.' . $parts['extension'];
                    // 从 ZIP 中提取文件到指定的目录，并重命名
                    // 将文件保存到新的位置
                    file_put_contents($move_to . $newFilename, $imageData);
                    $pic_info[] = ['name' => $parts['basename'], 'url' => '/media_library/' . $newFilename];
                }
            }
        }
    }

    $zip->close();

    return $pic_info;
}

//清空临时目录
function emptyDirectory($dir)
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $path = "$dir/$file";
            if (is_dir($path)) {
                emptyDirectory($path); // 递归删除子目录
                rmdir($path);
            } else {
                unlink($path); // 删除文件
            }
        }
    }
}

echo $jsonData->jsonData($code, $message, $obj);
