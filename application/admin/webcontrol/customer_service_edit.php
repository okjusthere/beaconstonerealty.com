<?php
//查看是否有访问权限
include_once '../checking_user.php';

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

$customer = array();
foreach ($data as $k => $val) {
    $val = object_array($val);
    if ($val["type"] == "image" && count($val["value"]) == 0) {
        $val["value"] = array();
    }

    $customer[] = $val;

}

$customer = json_encode($customer, JSON_UNESCAPED_UNICODE);

$filepath = '../config/customerservice.json'; //存放客服信息的文件
$res = file_put_contents($filepath, $customer); //获取将配置信息写入json文件中的结果
if ($res > 0) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}

echo $jsonData->jsonData($code, $message, $obj);
