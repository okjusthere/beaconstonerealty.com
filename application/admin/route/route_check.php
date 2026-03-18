<?php
//检查是否有重复的伪静态路由
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = json_decode(file_get_contents('php://input')); //获取非表单数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 初始化变量
$static_url = isset($data->static_url) ? $data->static_url : ''; //伪静态链接

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//当开启自定义伪静态时，判断伪静态链接是否唯一
if (haveSameStaticUrl($static_url)) {
    $code = 100;  //响应码
    $message = "伪静态 {$static_url} 已存在，换个试试吧";  //响应信息
}else{
    $code = 200;  //响应码
    $message = "success";  //响应信息
}

mysqli_close($link); //关闭数据库链接

// 返回响应
echo $jsonData->jsonData($code, $message, $obj);