<?php
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
$obj["data"] = array(
    array(
        'value' => '标准版',
        'label' => '标准版',
        'space_size' => 2
    ),
    array(
        'value' => '旗舰版',
        'label' => '旗舰版',
        'space_size' => 10
    ),
    array(
        'value' => '尊贵版',
        'label' => '尊贵版',
        'space_size' => 20
    )
);


echo $jsonData->jsonData($code, $message, $obj);
