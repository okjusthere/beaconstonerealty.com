<?php
// 复选框栏目放入回收站

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
if (!my_power("column_delete")) {
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
    // 1. 将子栏目改为顶级分类（使用预处理）
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql_update = "UPDATE column_list SET parentid = 0 WHERE parentid IN ($placeholders)";
    $stmt_update = $link->prepare($sql_update);
    
    if (!$stmt_update) {
        throw new Exception('子栏目更新准备失败: ' . $link->error);
    }
    
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $stmt_update->bind_param($types, ...$ids);
    
    if (!$stmt_update->execute()) {
        throw new Exception('子栏目更新执行失败: ' . $stmt_update->error);
    }
    $stmt_update->close();

    // 2. 将选定栏目放入回收站（使用预处理）
    $sql = "UPDATE column_list SET is_delete = 1 WHERE id IN ($placeholders)";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('回收站操作准备失败: ' . $link->error);
    }
    
    $stmt->bind_param($types, ...$ids);
    
    if (!$stmt->execute()) {
        throw new Exception('回收站操作执行失败: ' . $stmt->error);
    }
    
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($affectedRows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("导航菜单批量放入回收站，ID：" . implode(',', $ids));
        $link->commit();
    } else {
        $code = 404;
        $message = '未找到要更新的记录';
        $link->rollback();
    }
} catch (Exception $e) {
    $link->rollback();
    $code = 100;
    $message = '操作失败: ' . $e->getMessage();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);