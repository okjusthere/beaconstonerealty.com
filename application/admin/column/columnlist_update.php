<?php
// 更新栏目信息

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

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 验证必要字段
$requiredFields = ['value', 'id', 'field_name'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field)) {
        echo $jsonData->jsonData(400, '缺少必要参数: ' . $field, []);
        exit;
    }
}

// 提取并过滤数据
$id = (int)$data->id;
$field = trim($data->field_name);
$value = $data->value;

// 验证字段名是否合法
$allowedFields = ['title', 'sub_title', 'url', 'remarks', 'thumbnail', 'banner', 'is_show', 'seo_title', 'paixu', 'seo_keywords', 'seo_description', 'allow_access'];
if (!in_array($field, $allowedFields)) {
    echo $jsonData->jsonData(400, '不允许更新的字段', []);
    exit;
}

// 处理特殊字段
if ($field == "allow_access") {
    $value = (is_array($value) && !empty($value)) ? json_encode($value) : '';
} else {
    $value = is_string($value) ? trim($value) : $value;
}

$code = 500;
$message = '更新失败！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("column_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 准备预处理语句（使用白名单验证字段名）
$sql = "UPDATE column_list SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库更新准备失败: ' . $link->error, []);
    exit;
}

// 绑定参数
$stmt->bind_param("si", $value, $id);

// 执行更新
if ($stmt->execute()) {
    $code = 200;
    $message = 'success';
    // updatelogs("修改导航菜单，ID：" . $id);
} else {
    $code = 100;
    $message = 'error';
}

// 关闭语句和连接
$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);