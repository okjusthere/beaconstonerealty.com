<?php
//获取网站相关信息
//查看是否有访问权限
include_once 'checking_user.php';
include_once '../common.php'; //获取常量
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//通过接口验证，客户输入的账号密码是否正确
@$webinfo = file_get_contents(API_WEBINFO . $_SERVER['HTTP_HOST']);
$data = json_decode($webinfo, true);

if ($data["message"] == "success") {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
    $obj["data"] = $data["obj"]["data"];
}


echo $jsonData->jsonData($code, $message, $obj);
