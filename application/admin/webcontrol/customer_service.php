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

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$data = file_get_contents('../config/customerservice.json');
$data = json_decode($data, true);

$obj['data'] = $data;

echo $jsonData->jsonData($code, $message, $obj);
