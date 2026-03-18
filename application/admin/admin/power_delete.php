<?php
// 删除管理员角色

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
if (!my_power("power_delete")) {
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
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

if (!isset($data->id)) {
    echo $jsonData->jsonData(400, '缺少角色ID参数', []);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的角色ID', []);
    exit;
}

// 开始事务
$link->begin_transaction();

// 1. 先删除角色对应的所有用户
$sql_del_admin = "DELETE FROM admin WHERE power = ?";
$stmt_del_admin = $link->prepare($sql_del_admin);

if (!$stmt_del_admin) {
    $link->rollback();
    echo $jsonData->jsonData(500, '删除用户准备失败: ' . $link->error, []);
    exit;
}

$stmt_del_admin->bind_param("i", $id);
if (!$stmt_del_admin->execute()) {
    $stmt_del_admin->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '删除用户执行失败: ' . $stmt_del_admin->error, []);
    exit;
}

$stmt_del_admin->close();

// 2. 删除角色本身
$sql_del_power = "DELETE FROM admin_power WHERE id = ?";
$stmt_del_power = $link->prepare($sql_del_power);

if (!$stmt_del_power) {
    $link->rollback();
    echo $jsonData->jsonData(500, '删除角色准备失败: ' . $link->error, []);
    exit;
}

$stmt_del_power->bind_param("i", $id);
if (!$stmt_del_power->execute()) {
    $stmt_del_power->close();
    $link->rollback();
    echo $jsonData->jsonData(500, '删除角色执行失败: ' . $stmt_del_power->error, []);
    exit;
}

$affected_rows = $stmt_del_power->affected_rows;
$stmt_del_power->close();

// 处理结果
if ($affected_rows > 0) {
    $code = 200;
    $message = '角色删除成功';
    updatelogs("账号管理，删除账号角色，ID：" . $id);
    $link->commit(); // 提交事务
} else {
    $code = 404;
    $message = '未找到要删除的角色';
    $link->rollback(); // 回滚事务
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);