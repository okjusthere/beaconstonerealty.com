<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input');
$data = json_decode($data);

$field = "show_type"; // 要更新的字段
$id = isset($data->id) ? (int)$data->id : 0; // 分类ID
$value = isset($data->type) ? (int)$data->type : 1; // 字段值

$code = 500;
$message = '更新失败！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("proclass_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit;
}

// 准备SQL语句
if ($id > 0) {
    $sql = "UPDATE product_class SET {$field}=? WHERE id=? AND is_delete=0";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('ii', $value, $id);
        $success = $stmt->execute();
        $stmt->close();
    }
} else {
    $sql = "UPDATE product_class SET {$field}=?";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('i', $value);
        $success = $stmt->execute();
        $stmt->close();
    }
}

// 处理结果
if (isset($success) && $success) {
    $code = 200;
    $message = 'success';
} else {
    $code = 100;
    $message = 'error';
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);