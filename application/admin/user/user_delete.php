<?php
//按钮删除管理员

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

$code = 500;  //响应码
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("user_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 验证ID参数
if (!isset($data->id) || !is_numeric($data->id)) {
    $code = 400;  //响应码
    $message = '无效的用户ID！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$id = (int)$data->id;

// 使用预处理语句删除用户
$sql = 'DELETE FROM user WHERE id = ?';
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    
    if ($result) {
        $code = 200;
        $message = 'success';
        updatelogs("用户管理，删除用户，ID：" . $id); //记录操作日志
    } else {
        $code = 100;
        $message = '删除失败，请重试或联系管理员！';
    }
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);