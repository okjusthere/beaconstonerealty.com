<?php
//编辑网站控制
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 初始化响应数据
$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("webcontrol")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要参数
if (!isset($data->id) || !isset($data->state) || !isset($data->network_security) || !isset($data->tips)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备数据
$id = (int)$data->id;
$state = $data->state ? 1 : 2;
$network_security = $data->network_security ? 1 : 2;
$tips = $link->real_escape_string(trim($data->tips)); // 使用更安全的转义函数

// 使用预处理语句更新数据
$sql = "UPDATE web_control SET state = ?,network_security = ?, tips = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssi", $state, $network_security, $tips, $id);
    $result = $stmt->execute();

    if ($result) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 100;
        $message = '更新失败，请重试！';
    }
} else {
    $code = 500;
    $message = '数据库预处理失败';
}
$stmt->close();

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);