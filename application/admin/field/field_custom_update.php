<?php
// 更新文章信息
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

// 获取输入数据
$data = file_get_contents('php://input');
if ($data === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit();
}

$data = json_decode($data);
if ($data === null) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit();
}

// 验证必要字段
if (!isset($data->value, $data->id, $data->field_name)) {
    echo $jsonData->jsonData(400, '缺少必要参数', []);
    exit();
}

$value = $data->value;
$id = (int)$data->id;
$field = $data->field_name;

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("field_edit")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', []);
    exit();
}

// 字段名安全验证
if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
    echo $jsonData->jsonData(400, '字段名只能包含字母、数字和下划线', []);
    exit();
}

// 准备SQL语句
$sql = "UPDATE field_custom SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败', []);
    exit();
}

// 绑定参数并执行
$stmt->bind_param("si", $value, $id);
$result = $stmt->execute();
$stmt->close();

if (!$result) {
    echo $jsonData->jsonData(500, '数据库更新失败', []);
    exit();
}

// 成功响应
echo $jsonData->jsonData(200, 'success', []);

// 关闭数据库连接
mysqli_close($link);