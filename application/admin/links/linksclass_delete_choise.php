<?php
// 复选框 删除文章分类信息
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
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("linksclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    die();
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    die($jsonData->jsonData(400, '无效的请求数据', []));
}

// 验证ID数组
$ids = array_filter($data, 'is_numeric');
if (empty($ids)) {
    die($jsonData->jsonData(400, '无效的ID参数', []));
}

// 准备ID列表用于日志记录
$id_list = implode(',', $ids);

// 开始事务
$link->autocommit(false); // 关闭自动提交
$transactionSuccess = true;

// 创建预处理语句 - 更新子分类为顶级分类
$update_sql = "UPDATE tb_links_class SET parentid = 0 WHERE parentid IN (" . 
              implode(',', array_fill(0, count($ids), '?')) . ")";
$update_stmt = $link->prepare($update_sql);

// 创建预处理语句 - 删除分类
$delete_sql = "DELETE FROM tb_links_class WHERE id IN (" . 
              implode(',', array_fill(0, count($ids), '?')) . ")";
$delete_stmt = $link->prepare($delete_sql);

if (!$update_stmt || !$delete_stmt) {
    $message = '数据库预处理失败: ' . $link->error;
    $transactionSuccess = false;
} else {
    // 绑定更新语句参数
    $types = str_repeat('i', count($ids));
    $update_stmt->bind_param($types, ...$ids);
    
    // 绑定删除语句参数
    $delete_stmt->bind_param($types, ...$ids);
    
    // 执行更新
    if (!$update_stmt->execute()) {
        $code = 100;
        $message = '更新子分类失败: ' . $update_stmt->error;
        $transactionSuccess = false;
    }
    
    // 只有更新成功才执行删除
    if ($transactionSuccess) {
        if (!$delete_stmt->execute()) {
            $code = 100;
            $message = '删除失败: ' . $delete_stmt->error;
            $transactionSuccess = false;
        }
    }
    
    // 根据事务状态提交或回滚
    if ($transactionSuccess) {
        if ($link->commit()) {
            $code = 200;
            $message = 'success';
            updatelogs("友情链接，批量删除友链分类，ID：" . $id_list);
        } else {
            $code = 100;
            $message = '操作失败';
            $transactionSuccess = false;
        }
    }
    
    // 如果事务失败则回滚
    if (!$transactionSuccess) {
        $link->rollback();
    }
    
    // 关闭语句
    if ($update_stmt) $update_stmt->close();
    if ($delete_stmt) $delete_stmt->close();
}

// 恢复自动提交
$link->autocommit(true);

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);