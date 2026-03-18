<?php
//获取网站空间信息
//查看是否有访问权限
include_once '../checking_user.php';

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

header('Content-Type:application/json; charset=utf-8');

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

$webpackage = array(); //网站版本配置信息
$webpackage["webpackage"] = my_crypt($data->webpackage, 1);
$webpackage["spacesize"] = my_crypt($data->spacesize, 1);

if (count($webpackage) > 0) {
    $webpackage = json_encode($webpackage);
    $filepath = '../config/webpackage.json'; //存放配置信息的文件
    $res = file_put_contents($filepath, $webpackage); //获取将配置信息写入json文件中的结果
    if ($res > 0) {
        $code = 200;  //响应码
        $message = 'success';  //响应信息
    }
}

echo $jsonData->jsonData($code, $message, $obj);
