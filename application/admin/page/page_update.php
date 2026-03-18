<?php
//更新文章信息
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

// 初始化变量
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
if (!my_power("page_edit")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 验证ID有效性
if ($id <= 0) {
    $code = 400;
    $message = '无效的页面ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}


// 使用预处理语句
$sql = "UPDATE news SET `$field` = ? WHERE id = ?";
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

if ($result && $affected_rows > 0) {
    $code = 200;
    $message = 'success';
} else {
    $code = ($affected_rows === 0) ? 401 : 500;
    $message = ($affected_rows === 0) ? '数据未发生变化，无需更新' : '更新操作执行失败';
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);