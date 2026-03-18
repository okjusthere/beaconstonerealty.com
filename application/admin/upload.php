<?php
//header('Content-Type:application/json; charset=utf-8'); //返回json
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 文件上传函数
include 'upload_file_function.php';
header('Content-Type:application/json; charset=utf-8');

$file = $_FILES['file']; // 获取图片信息
$allow_type = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/zip', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-zip-compressed');
$allow_format = array('jpg', 'png', 'jpeg', 'docx', 'xlsx', 'zip', 'pdf', 'doc', 'xls');
$path = '../../media_library';

//获取用户输入的用户名、密码、验证码
$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

if ($filename = upload_single($file, $allow_type, $path, $error, $allow_format, $max_size = 52428800)) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
    $obj = $filename;
} else {
    $code = 100;
    $message = $error;
}

//返回json
echo $jsonData->jsonData($code, $message, $obj);
