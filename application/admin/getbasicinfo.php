<?php
//获取网站相关信息
//查看是否有访问权限
include_once 'checking_user.php';
include_once '../common.php'; //获取常量
//引用自定义函数
include_once "function.php";

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

$data = file_get_contents('config/backgroundinfo.json'); //获取后台信息
$data = json_decode($data, true);

//判断返回的数据中state键对应的值是否为true，若为true后台调用自定义的信息，若为false远程调用统一的后台信息
if (isset($data["state"]) && $data["state"]) {
    $data["background_bg"] = getThumbnailPath($data["background_bg"]);
    $data["background_logo"] = getThumbnailPath($data["background_logo"]);

    $code = 200;  //响应码
    $message = 'success';  //响应信息
    $obj["data"] = $data;
} else {
    //通过接口验证，客户输入的账号密码是否正确
    @$basicinfo = file_get_contents(API_BG_INFO);
    $data = json_decode($basicinfo, true);

    $data["background_bg"] = SITEMANAGE_URL . $data["background_bg"];
    $data["background_logo"] = SITEMANAGE_URL . $data["background_logo"];

    $code = 200;  //响应码
    $message = 'success';  //响应信息

    $obj["data"] = $data;
}

echo $jsonData->jsonData($code, $message, $obj);
