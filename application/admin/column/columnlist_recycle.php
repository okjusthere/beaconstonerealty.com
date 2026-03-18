<?php
// 栏目放入回收站

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
if (!my_power("column_delete")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit;
}

$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据格式', []);
    exit;
}

if (!isset($data->id)) {
    echo $jsonData->jsonData(400, '缺少栏目ID参数', []);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的栏目ID', []);
    exit;
}

// 开始事务
$link->begin_transaction();

// 1. 检查是否有子分类
$checkSql = 'SELECT id FROM column_list WHERE parentid = ?';
$checkStmt = $link->prepare($checkSql);

if (!$checkStmt) {
    $link->rollback();
    echo $jsonData->jsonData(500, '查询准备失败: ' . $link->error, []);
    exit;
}

$checkStmt->bind_param("i", $id);
if (!$checkStmt->execute()) {
    $checkStmt->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '查询执行失败: ' . $checkStmt->error, []);
    exit;
}

$result = $checkStmt->get_result();
$hasChildren = $result->num_rows > 0;
$checkStmt->close();

if ($hasChildren) {
    $link->rollback();
    echo $jsonData->jsonData(400, '请先删除该栏目下的子栏目', []);
    exit;
}

// 2. 执行放入回收站操作
$updateSql = 'UPDATE column_list SET is_delete = 1 WHERE id = ?';
$updateStmt = $link->prepare($updateSql);

if (!$updateStmt) {
    $link->rollback();
    echo $jsonData->jsonData(500, '更新准备失败: ' . $link->error, []);
    exit;
}

$updateStmt->bind_param("i", $id);
if (!$updateStmt->execute()) {
    $updateStmt->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '更新执行失败: ' . $updateStmt->error, []);
    exit;
}

$affectedRows = $updateStmt->affected_rows;
$updateStmt->close();

// 处理结果
if ($affectedRows > 0) {
    $code = 200;
    $message = 'success';
    updatelogs("导航菜单放入回收站，ID：" . $id);
    $link->commit();
} else {
    $code = 404;
    $message = '未找到要操作的栏目';
    $link->rollback();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);