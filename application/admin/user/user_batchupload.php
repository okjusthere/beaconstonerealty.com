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
$path = count($data->path) == 0 ? '' : object_array($data->path[0]); //用户信息表

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');
//判断后台用户权限
if (!my_power("user_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$fileXlsx = "../.." . $path["url"]; //表格文件名

if (file_exists($fileXlsx)) {
    $excelData = $excel::readExcel($fileXlsx); //excel表格信息

    $insert_success_id = array(); //插入成功的ID
    $insert_error_num = array(); //插入失败的Excel编号
    $have_user = []; //已存在的用户
    $error_row = []; //失败的行号
    if (count($excelData) > 1) {
        //整理存入数据库的数据
        foreach ($excelData as $key => $value) {
            if ($key > 0) {
                $number = $value['A']; //Excel表中的编号
                $power = empty($value['B']) ? 1 : $value['B']; //角色ID
                $user_name = $value['C']; //用户名
                $user_mobile = $value['D']; //手机号
                $user_password = empty($value['E']) ? '' : my_crypt($value['E'], 1); //密码，不为空时加密
                $add_time = time(); //添加时间

                if (!empty($user_name) && !empty($user_password)) {
                    $sql_have = "SELECT * FROM user WHERE user_name=? LIMIT 1"; //判断当前账号有没有注册过
                    $stmt_have = $link->prepare($sql_have);
                    $stmt_have->bind_param("s", $user_name);
                    $stmt_have->execute();
                    // 显式存储结果集（必须调用）
                    $stmt_have->store_result();

                    if ($stmt_have->affected_rows > 0) {
                        $sql_update = "UPDATE user SET power=?,user_mobile=?,user_password=?,add_time=? WHERE user_name=?";
                        $stmt_update = $link->prepare($sql_update);
                        $stmt_update->bind_param("issss", $power, $user_mobile, $user_password, $add_time, $user_name);
                        if ($stmt_update->execute()) $have_user[] = $user_name;
                        $stmt_update->close();
                    } else {
                        $sql = "INSERT INTO user (power,user_name,user_mobile,user_password,add_time) VALUES (?,?,?,?,?)";
                        $stmt = $link->prepare($sql);
                        $stmt->bind_param("issss", $power, $user_name, $user_mobile, $user_password, $add_time);
                        if ($stmt->execute()) {
                            $insert_success_id[] = $stmt->insert_id; //当前新增记录的ID
                        } else {
                            $error_row[] = $key + 1;
                            $obj["data"]["error"][] = "Error: " . $sql . "，信息：" . $link->error;
                        }
                        $stmt->close();
                    }
                    // 释放结果集（关键！）
                    $stmt_have->free_result();
                    $stmt_have->close();
                } else {
                    $error_row[] = $key + 1;
                }
            }
        }

        if (count($insert_success_id) > 0 || count($have_user) > 0 || count($error_row) > 0) {
            $code = 200; //响应码
            $message = 'success'; //响应信息
            $total = count($excelData) - 1; //总共需要插入的记录条数
            $error_tips = count($error_row) > 0 ? '，失败' . count($error_row) . '条，失败行号' . implode(',', $error_row) : '';
            $obj["data"]["success"] = '共有' . $total . '条数据需要插入，插入' . count($insert_success_id) . '条，更新' . count($have_user) . '条' . $error_tips;
            $obj["data"]["success_num"] = count($insert_success_id);
            $updateTips = "用户管理，通过user_list.xlsx批量导入用户，ID：" . implode(',', $insert_success_id); //操作日志事件内容
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

if (file_exists($fileXlsx)) {
    unlink($fileXlsx); //删除文件，避免占用太多空间
}

echo $jsonData->jsonData($code, $message, $obj);
