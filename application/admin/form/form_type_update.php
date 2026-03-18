<?php
// 更新文章分类信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;

include_once '../../../myclass/ResponseJson.php';

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->value, $data->id, $data->field_name)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少必要参数', []);
    exit;
}

// 处理输入数据
$id = (int)$data->id;
$value = $data->value;
$field = $data->field_name;

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("form_type_edit")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', []);
    exit;
}

// 验证ID有效性
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的表单分类ID', []);
    exit;
}

// 使用预处理语句更新数据
$sql = "UPDATE tb_form_type SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败', []);
    exit;
}

$stmt->bind_param("si", $value, $id);
$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result) {
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("表单分类字段更新：{$field}，ID：" . $id);
    } else {
        $code = 200;
        $message = 'success';
    }
} else {
    $code = 500;
    $message = '更新失败';
}

mysqli_close($link);

echo $jsonData->jsonData($code, $message, []);