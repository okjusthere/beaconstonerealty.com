<?php
//获取网站中的一些参数
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

$filepath = '../config/parameter.json'; //存放参数信息的文件
$data = file_get_contents($filepath); //从参数的json文件中获取数据
$data = json_decode($data, true);

$obj['data'] = [];
/*
 * parameter_key--参数名
 * parameter_value--参数值
 * parameter_description--参数描述*/
foreach ($data as $k => $val) {
    if ($val['parameter_key'] !== "state") { //不需要对自定义状态加密
        $val['parameter_value'] = empty($val['parameter_value']) ? '' : my_crypt($val['parameter_value'], 2);
    }

    $obj['data'][] = $val;
}

echo $jsonData->jsonData($code, $message, $obj);
