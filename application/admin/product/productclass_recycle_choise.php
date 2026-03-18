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

$code = 500;
$message = '删除失败，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("proclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit;
}

$data = file_get_contents('php://input');
$data = json_decode($data);

// 验证输入数据
if (!is_array($data) || empty($data)) {
    echo $jsonData->jsonData(400, '无效的删除请求', $obj);
    exit;
}

// 过滤和验证ID
$ids = array_filter($data, function($id) {
    return is_numeric($id) && $id > 0;
});

if (empty($ids)) {
    echo $jsonData->jsonData(400, '无效的分类ID', $obj);
    exit;
}

// 开始事务
$link->begin_transaction();

// 1. 将子分类提升为顶级分类
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql_update = "UPDATE product_class SET parentid=0 WHERE parentid IN ($placeholders)";
$stmt_update = $link->prepare($sql_update);

if (!$stmt_update) {
    $link->rollback();
    echo $jsonData->jsonData(500, 'SQL预处理失败: ' . $link->error, $obj);
    exit;
}

$types = str_repeat('i', count($ids));
$bind_result = $stmt_update->bind_param($types, ...$ids);

if (!$bind_result || !$stmt_update->execute()) {
    $stmt_update->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '更新子分类失败: ' . $stmt_update->error, $obj);
    exit;
}
$stmt_update->close();

// 2. 将指定分类放入回收站
$sql_delete = "UPDATE product_class SET is_delete=1 WHERE id IN ($placeholders)";
$stmt_delete = $link->prepare($sql_delete);

if (!$stmt_delete) {
    $link->rollback();
    echo $jsonData->jsonData(500, 'SQL预处理失败: ' . $link->error, $obj);
    exit;
}

$bind_result = $stmt_delete->bind_param($types, ...$ids);

if (!$bind_result || !$stmt_delete->execute()) {
    $stmt_delete->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '删除分类失败: ' . $stmt_delete->error, $obj);
    exit;
}

$affected_rows = $stmt_delete->affected_rows;
$stmt_delete->close();

if ($affected_rows <= 0) {
    $link->rollback();
    echo $jsonData->jsonData(404, '未找到要删除的分类', $obj);
    exit;
}

// 提交事务
if (!$link->commit()) {
    echo $jsonData->jsonData(500, '事务提交失败', $obj);
    exit;
}

$code = 200;
$message = 'success';
updatelogs("产品管理，产品分类批量放入回收站，ID：" . implode(',', $ids));

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);