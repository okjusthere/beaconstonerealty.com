<?php
//获取网站中的一些参数
//查看是否有访问权限
include_once '../checking_user.php';
include_once '../../common.php'; //获取常量
//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

@$data = file_get_contents(API_MARKING); //存放参数信息的文件，并从参数的json文件中获取数据
$data = json_decode($data, true);

$obj['data'] = [];
$obj['contact'] = $data["contact"];
/*
 * parameter_key--参数名
 * parameter_value--参数值
 * parameter_description--参数描述*/
foreach ($data["data"] as $k => $val) {
    $obj['data'][] = $val;
}

echo $jsonData->jsonData($code, $message, $obj);
