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

$code = 500;  // 默认响应码
$message = '删除失败，请重试！';  // 默认响应信息
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_delete")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit;
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', $obj);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少ID参数', $obj);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的表单分类ID', $obj);
    exit;
}

// 使用事务确保数据一致性
$link->begin_transaction();

// 1. 先删除分类下的所有表单（使用预处理）
$sql_form = "DELETE FROM tb_form WHERE type_id = ?";
$stmt_form = $link->prepare($sql_form);
if (!$stmt_form) {
    $link->rollback();
    echo $jsonData->jsonData(500, '删除表单准备失败: ' . $link->error, $obj);
    exit;
}

$stmt_form->bind_param("i", $id);
if (!$stmt_form->execute()) {
    $stmt_form->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '删除表单执行失败: ' . $stmt_form->error, $obj);
    exit;
}
$stmt_form->close();

// 2. 删除分类本身（使用预处理）
$sql_type = "DELETE FROM tb_form_type WHERE id = ?";
$stmt_type = $link->prepare($sql_type);
if (!$stmt_type) {
    $link->rollback();
    echo $jsonData->jsonData(500, '删除分类准备失败: ' . $link->error, $obj);
    exit;
}

$stmt_type->bind_param("i", $id);
if (!$stmt_type->execute()) {
    $stmt_type->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '删除分类执行失败: ' . $stmt_type->error, $obj);
    exit;
}

$affected_rows = $stmt_type->affected_rows;
$stmt_type->close();

// 处理结果
if ($affected_rows > 0) {
    $code = 200;
    $message = 'success';
    updatelogs("在线表单，删除表单分类以及相关分类下的表单信息，ID：" . $id);
    $link->commit(); // 提交事务
} else {
    $code = 404;
    $message = '未找到要删除的分类';
    $link->rollback(); // 回滚事务
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);