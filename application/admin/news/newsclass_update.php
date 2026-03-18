<?php
//更新文章分类信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input');
$data = json_decode($data);

// 验证和初始化变量
$id = isset($data->id) ? (int)$data->id : 0;
$field = isset($data->field_name) ? $data->field_name : '';
$value = isset($data->value) ? $data->value : '';

// 处理allow_access字段
if ($field === "allow_access") {
    $value = (is_array($value) || is_object($value)) && count((array)$value) > 0 
        ? json_encode($value) 
        : '';
}

$code = 500;
$message = '更新失败！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("newsclass_edit")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 验证ID有效性
if ($id <= 0) {
    $code = 400;
    $message = '无效的分类ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 验证字段名有效性
$allowedFields = ['title', 'description', 'thumbnail', 'banner', 'is_show', 'is_delete', 'paixu', 'allow_access'];
if (!in_array($field, $allowedFields)) {
    $code = 400;
    $message = '不允许更新的字段';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句
$sql = "UPDATE news_class SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库操作准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
$stmt->bind_param("si", $value, $id);

// 执行更新
$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result) {
    if ($affected_rows > 0) {
        echo $jsonData->jsonSuccessData($obj);
    } else {
        $code = 404;
        $message = '未找到要更新的分类';
        echo $jsonData->jsonData($code, $message, $obj);
    }
} else {
    $code = 500;
    $message = '更新操作执行失败';
    echo $jsonData->jsonData($code, $message, $obj);
}

mysqli_close($link);