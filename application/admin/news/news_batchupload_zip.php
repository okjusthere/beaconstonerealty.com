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

require_once '../../../vendor/autoload.php';
require_once('../../../myclass/Excel.php');

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Paragraph;

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$excel = new \myclass\Excel();

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并解用json_decode码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}
$path = count($data->path) == 0 ? '' : object_array($data->path[0]); //新闻信息压缩包

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');
//判断后台用户权限
if (!my_power("news_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit; //终止继续执行
}

$fileZip = "../../.." . $path["url"]; //压缩包文件名
$fileExcel = "list.xlsx"; //数据Excel表

if (!file_exists($fileZip)) {
    echo $jsonData->jsonData(400, '文件不存在，请检查后重新上传！');
    exit;
}

try {
    $excelData = $excel::readExcelFromZip($fileZip, $fileExcel); //excel表格信息

    if (count($excelData) <= 2) throw new Exception('文件数据为空，请检查后重新上传！');

    $insert_success_id = array(); //插入成功的ID
    $insert_error_num = array(); //插入失败的Excel编号
    $field_title_xlsx = array(); //获取list.xlsx表格中有哪些自定义字段
    $field_custom = getFieldCustom(); //获取产品自定义信息字段

    //获取自定义字段信息
    foreach ($excelData as $key => $value) {
        if ($key === 1) {
            foreach ($value as $k => $val) {
                if ($k > 9) {
                    $field_title_xlsx[] = $val;
                }
            }
        }
    }

    // 开启事务
    mysqli_autocommit($link, false);

    //整理存入数据库的数据
    foreach ($excelData as $key => $value) {
        if ($key > 1) {
            $number = $value[0]; //Excel表中的编号
            $class_id = empty($value[1]) ? '' : json_encode(explode(',', $value[1]));
            $title = $value[2]; //文章标题
            $t_info = empty($value[3]) ? array() : getThumbnail($value[3]); //获取缩略图数组
            $thumbnail = count($t_info) == 0 ? '' : json_encode($t_info, JSON_UNESCAPED_UNICODE); //缩略图
            $p_info = empty($value[4]) ? array() : getPhotoAlbum($value[4]); //获取图片相册数组
            $photo_album = count($p_info) == 0 ? '' : json_encode($p_info, JSON_UNESCAPED_UNICODE); //图片相册
            $e_info = empty($value[5]) ? array() : getEnclosure($value[5]); //获取附件数组
            $enclosure = count($e_info) == 0 ? '' : json_encode($e_info, JSON_UNESCAPED_UNICODE); //附件
            $content = empty($value[6]) ? '' : getContent($value[6]); //产品详情
            $url = $value[7]; //链接地址
            $keywords = $value[8]; //关键词
            $description = $value[9]; //描述
            $add_time = time(); //添加时间
            $seo_title = '';
            $seo_keywords = '';
            $seo_description = '';
            $field = [];

            if (count($field_custom) > 0) { //当存在自定义字段时，拼凑自定义字段信息
                $filed_info = []; //字段信息
                if (count($field_title_xlsx) > 0) {
                    foreach ($field_title_xlsx as $k => $val) {
                        $filed_info[$val] = $value[10 + $k];
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

            $sql = "insert into news (classid,title,thumbnail,photo_album,enclosure,content,url,keywords,description,add_time,seo_title,seo_keywords,seo_description) values (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $link->prepare($sql);
            if (!$stmt) throw new Exception('发布文章，SQL准备失败: ' . $link->error);

            $stmt->bind_param("sssssssssssss", $class_id, $title, $thumbnail, $photo_album, $enclosure, $content, $url, $keywords, $description, $add_time, $seo_title, $seo_keywords, $seo_description);
            if (!$stmt->execute()) throw new Exception('发布文章，SQL执行失败: ' . $link->error);

            $record_id = $stmt->insert_id; //当前新增记录的ID
            $insert_success_id[] = $record_id;

            $class_id_ary = json_decode($class_id);
            $detail_template_id = getDetailTemplateId('news_class', $class_id_ary[0]); //获取第一个分类对应的详情模板ID
            $route_page = getRoutePage($detail_template_id); //获取路由页面
            if (!empty($route_page)) {
                //插入路由规则-获取对应记录的ID
                $rule_id = insertRewriteRules($route_page . '/' . $record_id, $detail_template_id, '1', json_encode(["id" => $record_id]));
                if ($rule_id > 0) {
                    //更新文章路由规则ID，rule_id
                    updateRecord('news', [$record_id], ["id"], ["rule_id" => $rule_id]);
                }
            }

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
                if (!$res_field_info) throw new Exception('发布文章，自定义字段添加失败！ ');
            }
        }
    }

    $total = count($excelData) - 2; //总共需要插入的记录条数
    $obj["data"]["success"] = '共有' . $total . '条数据需要插入，成功' . count($insert_success_id) . '条，失败' . count($insert_error_num) . '条';
    $obj["data"]["success_num"] = count($insert_success_id);
    $obj["data"]["error_num"] = $insert_error_num;
    $updateTips = "文章管理，通过zip压缩包批量添加文章，ID：" . implode(',', $insert_success_id); //操作日志事件内容
    updatelogs($updateTips); //记录操作日志

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';

} catch (Exception $e) {
    // 回滚事务
    if (isset($link)) {
        mysqli_rollback($link);
        mysqli_autocommit($link, true);
    }

    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (file_exists($fileZip)) unlink($fileZip); //删除压缩包
}

//关闭数据库链接
mysqli_close($link);

//获取自定义字段信息
function getFieldCustom(): array
{
    $field_custom = [];
    //sql查询语句
    $sql = "select * from field_custom where table_name='news'";
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
    $move_to = '../../../media_library/'; // 图片要转移的目录
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

//获取图片相册的图片
function getPhotoAlbum($fileName): array
{
    global $fileZip; // 压缩包文件
    $filePhotoAlbum = "photo_album/{$fileName}/"; // 指定想要获取图片的文件夹路径（ZIP包内的路径）
    $move_to = '../../../media_library/'; // 图片要转移的目录
    $pic_info = array(); //返回新的图片信息

    $zip = new ZipArchive();
    if ($zip->open($fileZip) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name']; //获取文件名称
            // 检查对应目录是否有图片（通过检查文件签名）
            if (substr($filename, 0, strlen($filePhotoAlbum)) === $filePhotoAlbum && preg_match('/\.(jpg|jpeg|png|gif)$/i', $filename)) {
                // 获取图片内容
                $imageData = $zip->getFromName($filename);

                // 获取原始文件名和文件扩展名
                $parts = pathinfo($filename);
                // 新的文件名（这里简单使用了索引，你可以根据自己的需求来定制命名规则）
                $newFilename = 'image_' . date('YmdHis') . chr(mt_rand(65, 90)) . '.' . $parts['extension'];
                // 从 ZIP 中提取文件到指定的目录，并重命名
                // 将文件保存到新的位置
                file_put_contents($move_to . $newFilename, $imageData);
                $pic_info[] = ['name' => $parts['basename'], 'url' => '/media_library/' . $newFilename];
            }
        }
    }

    $zip->close();
    return $pic_info;
}

//获取附件信息
function getEnclosure($fileName): array
{
    global $fileZip; // 压缩包文件
    $fileEnclosure = "enclosure/{$fileName}/"; // 指定想要获取附件的文件夹路径（ZIP包内的路径）
    $move_to = '../../../media_library/'; // 附件要转移的目录
    $temp = '../../../media_library/temp/'; //临时解压目录
    $enclosure_info = array(); //返回新的图片信息

    $zip = new ZipArchive();
    if ($zip->open($fileZip) === TRUE) {
        // 解压ZIP文件到临时目录
        $zip->extractTo($temp);
        $zip->close();

        if (is_dir($temp . $fileEnclosure)) {
            // 获取解压后的文件列表
            $files = scandir($temp . $fileEnclosure);

            foreach ($files as $key => $file) {
                // 获取原始文件名和文件扩展名
                $parts = pathinfo($file);
                $filename = $parts["basename"]; //文件名称
                $file_extension = $parts["extension"]; //文件后缀名
                if (preg_match('/\.(zip|pdf|docx|doc|xlsx|xls)$/i', $filename)) {
                    $newFilename = 'application_' . date('YmdHis') . chr(mt_rand(65, 90)) . '.' . $file_extension; //获取新的文件名
                    rename($temp . $fileEnclosure . $filename, $move_to . $newFilename);

                    $enclosure_info[] = ['name' => $filename, 'url' => '/media_library/' . $newFilename]; //返回新的文件信息
                }
            }
        }
    }
    emptyDirectory($temp); // 删除临时目录里面的内容
    return $enclosure_info;
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

//获取文章内容
function getContent($proTitle): string
{
    global $fileZip; // 压缩包文件
    $fileContent = "content/"; // 指定想要获取图片的文件夹路径（ZIP包内的路径）
    $temp_file = '../../../media_library/temp/'; //临时地址
    $content = ''; //产品详情信息

    $zip = new ZipArchive();
    if ($zip->open($fileZip) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name']; //获取文件名称

            // 检查文件是否为docx文档（通过检查文件签名）
            if (strpos($filename, $fileContent) === 0 && preg_match('/\.(docx)$/i', $filename)) {
                // 获取原始文件名和文件扩展名
                $parts = pathinfo($filename);
                if ($parts['filename'] === $proTitle) {
                    $zip->extractTo($temp_file, $filename);
                    $phpWord = IOFactory::load($temp_file . $filename, 'Word2007');
                    $content = docxHtml($phpWord);
                    unlink($temp_file . $filename);
                }
            }
        }
    }

    $zip->close();
    return $content;
}

//处理word文件内容
function docxHtml($phpWord)
{
    $html = '';
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $ele1) {
            if ($ele1 instanceof \PhpOffice\PhpWord\Element\Table) {
                $html .= getTableNode($ele1);
            } else {
                $paragraphStyle = $ele1->getParagraphStyle();
                if ($paragraphStyle) {
                    $textAlign = $paragraphStyle->getAlignment();
                    $textIndent = $paragraphStyle->getIndent();
                    $ParagraphStyle = '';
                    $textAlign && $ParagraphStyle .= "text-align:{$textAlign};";
                    $textIndent && $ParagraphStyle .= "text-indent:{$textIndent}em;";
                    $html .= '<p style="' . $ParagraphStyle . '">';
                } else {
                    $html .= '<p>';
                }
                if ($ele1 instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($ele1->getElements() as $ele2) {
                        if ($ele2 instanceof \PhpOffice\PhpWord\Element\Text) {
                            $html .= getTextNode($ele2);
                        } elseif ($ele2 instanceof \PhpOffice\PhpWord\Element\Image) {
                            $html .= getImageNode($ele2);
                        } elseif ($ele2 instanceof \PhpOffice\PhpWord\Element\Table) {
                            $html .= getTableNode($ele2);
                        }
                    }
                }
                $html .= '</p>';
            }
        }
    }

    return mb_convert_encoding($html, 'UTF-8', 'GBK');
}

//处理word表格
function getTableNode($node): string
{
    $return = '<table style="width: 100%">';

    foreach ($node->getRows() as $eleR) {
        $return .= '<tr>';
        foreach ($eleR->getCells() as $eleC) {
            $return .= '<td>';
            foreach ($eleC->getElements() as $ele) {
                $return .= getTextNode($ele);
            }
            $return .= '</td>';
        }
        $return .= '</tr>';
    }
    $return .= '</table>';
    return $return;
}

/**
 * 获取文档节点内容
 * @param $node
 * @return string
 */
function getTextNode($node): string
{
    $return = '';
    //处理文本
    if ($node instanceof \PhpOffice\PhpWord\Element\Text) {
        $style = $node->getFontStyle();
        $fontFamily = empty($style->getName()) ? '' : mb_convert_encoding($style->getName(), 'GBK', 'UTF-8');
        $fontSize = $style->getSize();
        $fontColor = $style->getColor();
        $isBold = $style->isBold();
        $styleString = '';
        $fontFamily && $styleString .= "font-family:{$fontFamily};";
        $fontSize && $styleString .= "font-size:{$fontSize}px;";
        $fontColor && $styleString .= "color:#{$fontColor};";
        $isBold && $styleString .= "font-weight:bold;";
        $return .= sprintf('<span style="%s">%s</span>', $styleString, mb_convert_encoding($node->getText(), 'GBK', 'UTF-8'));
    } //处理图片
    else if ($node instanceof \PhpOffice\PhpWord\Element\Image) {
        $return .= getImageNode($node);
    } //处理文本元素
    else if ($node instanceof \PhpOffice\PhpWord\Element\TextRun) {
        foreach ($node->getElements() as $ele) {
            $return .= getTextNode($ele);
        }
    }
    return $return;
}

//处理word中的图片
function getImageNode($node): string
{
    $return = '';
    $time_dir = date('Ymd', time()); //时间目录
    $img_dir = "../../../media_library/upload/image/{$time_dir}/"; //详情图片目录
    if (!is_dir($img_dir)) { //目录如果不存在，创建相关目录
        mkdir($img_dir, 0777, true);
    }
    $imageSrc = $img_dir . md5($node->getSource()) . "." . $node->getImageExtension();
    $imageData = $node->getImageStringData(true);
    file_put_contents($imageSrc, base64_decode($imageData));
    $return .= '<img src="/' . $imageSrc . '" style="max-width:100%;height:auto;">';
    return $return;
}

echo $jsonData->jsonData($code, $message, $obj);
