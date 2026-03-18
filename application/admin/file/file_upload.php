<?php
//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 文件上传函数
include 'file_upload_function.php';

$file = $_FILES['file']; // 获取图片信息

//允许上传的类型
$allow_type = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'text/css', 'text/html', 'text/javascript', 'application/x-javascript','image/x-icon','text/plain');
//允许上传的格式
$allow_format = array('jpg', 'png', 'jpeg');
//文件上传路径
$path = isset($_GET["path"]) ? "../../../" . $_GET["path"] : "../../../";

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("file_upload")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

if ($fileinfo = upload_single($file, $allow_type, $path, $error, $allow_format = array(), $max_size = 2000000)) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
    $obj = $fileinfo;
} else {
    $code = 100;
    $message = $error;
}

//关闭数据库链接
mysqli_close($link);

//返回json
echo $jsonData->jsonData($code, $message, $obj);
