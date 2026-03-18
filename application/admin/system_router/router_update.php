<?php
// 更新后台路由(单条)
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

header('Content-Type:application/json; charset=utf-8');

 if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
 if (empty($_SESSION["manager_username"]) && my_crypt($_SESSION["manager_username"], 2) != '13812345678') {
     echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
     exit;
 }

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 验证必要字段
$requiredFields = ['value', 'id', 'field_name'];
foreach ($requiredFields as $f) {
    if (!isset($data->$f)) {
        echo $jsonData->jsonData(400, '缺少必要参数: ' . $f, []);
        exit;
    }
}


$code = 500;
$message = '更新失败！';
$obj = [];

// 提取并过滤数据
$id = (int)$data->id;
$field = trim($data->field_name);
$value = $data->value;

try {
    // 处理特殊字段
    if ($field == "sort") {
        $type = 'ii';
    } else {
        $type = 'si';
    }

    $sql = "UPDATE tb_system_router SET `{$field}` = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('更新路由准备失败：' . $link->error);
    $stmt->bind_param($type, $value, $id);
    if (!$stmt->execute()) throw new Exception('更新路由执行失败：' . $stmt->error);
    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

// 关闭语句和连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message);