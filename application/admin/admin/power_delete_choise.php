<?php
// 批量删除管理员角色

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

$code = 500;  // 响应码
$message = '删除失败，请重试！';  // 响应信息
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("power_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}

// 验证并过滤ID数组
$ids = array_filter(array_map('intval', $data), function($id) {
    return $id > 0;
});

if (empty($ids)) {
    echo $jsonData->jsonData(400, '没有提供有效的ID', []);
    exit;
}

// 开始事务处理
$link->begin_transaction();

try {
    // 1. 先删除角色对应的所有用户（使用预处理）
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql_del_admin = "DELETE FROM admin WHERE power IN ($placeholders)";
    $stmt_del_admin = $link->prepare($sql_del_admin);
    
    if (!$stmt_del_admin) {
        throw new Exception('删除关联用户准备失败: ' . $link->error);
    }
    
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $stmt_del_admin->bind_param($types, ...$ids);
    
    if (!$stmt_del_admin->execute()) {
        throw new Exception('删除关联用户执行失败: ' . $stmt_del_admin->error);
    }
    $stmt_del_admin->close();

    // 2. 删除角色本身（使用预处理）
    $sql_del_power = "DELETE FROM admin_power WHERE id IN ($placeholders)";
    $stmt_del_power = $link->prepare($sql_del_power);
    
    if (!$stmt_del_power) {
        throw new Exception('删除角色准备失败: ' . $link->error);
    }
    
    $stmt_del_power->bind_param($types, ...$ids);
    
    if (!$stmt_del_power->execute()) {
        throw new Exception('删除角色执行失败: ' . $stmt_del_power->error);
    }
    
    $affected_rows = $stmt_del_power->affected_rows;
    $stmt_del_power->close();

    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("账号管理，批量删除账号角色，ID：" . implode(',', $ids));
        $link->commit(); // 提交事务
    } else {
        $code = 404;
        $message = '未找到匹配的记录';
        $link->rollback(); // 回滚事务
    }
} catch (Exception $e) {
    $link->rollback(); // 发生异常时回滚
    $code = 100;
    $message = 'error';
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);