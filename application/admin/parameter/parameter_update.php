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

$data_update = file_get_contents('php://input'); //获取非表单数据;
$data_update = json_decode($data_update); //解码axios传递过来的json数据

$value = $data_update->value; //新的参数值
$key = $data_update->key; //参数值对应的参数key

$filepath = '../config/parameter.json'; //存放参数信息的文件
$data = file_get_contents($filepath); //获取当前参数信息
$data = json_decode($data, true);

//要保存进参数文件的内容
$data_save = [];
/*
 * parameter_key--参数名
 * parameter_value--参数值
 * parameter_description--参数描述*/
foreach ($data as $k => $val) {
    if ($val['parameter_key'] === $key) {
        if ($val['parameter_key'] === "state") { //不需要对自定义状态加密
            $val['parameter_value'] = $value;
        } else {
            $val['parameter_value'] = empty($value) ? '' : my_crypt($value, 1);
        }
    }
    $data_save[] = $val;
}

$data_save = json_encode($data_save, JSON_UNESCAPED_UNICODE);
$res = file_put_contents($filepath, $data_save); //获取将配置信息写入json文件中的结果
if ($res > 0) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}

echo $jsonData->jsonData($code, $message, $obj);
