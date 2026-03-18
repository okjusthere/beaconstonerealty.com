<?php
// 复选框删除管理员
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;
include_once '../../../myclass/ResponseJson.php';

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '删除失败，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("field_delete")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$data = file_get_contents('php://input');
$data = json_decode($data);

// 验证并过滤ID数组
$ids = array_filter(array_map('intval', (array)$data));
if (empty($ids)) {
    $code = 400;
    $message = '请选择要删除的项';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 创建占位符字符串 (?,?,?...)
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// 第一步：删除字段信息表中的相关记录（使用JOIN优化）
$sql_field_info = "DELETE fi FROM field_info fi JOIN field_custom fc ON fi.table_name = fc.table_name AND fi.field_name = fc.field_name WHERE fc.id IN ($placeholders)";

$stmt_field_info = $link->prepare($sql_field_info);

if (!$stmt_field_info) {
    $code = 500;
    $message = '数据库操作准备失败: ' . $link->error;
} else {
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $stmt_field_info->bind_param($types, ...$ids);
    
    if ($stmt_field_info->execute()) {
        // 第二步：删除自定义字段表中的记录
        $sql = "DELETE FROM field_custom WHERE id IN ($placeholders)";
        $stmt = $link->prepare($sql);
        
        if (!$stmt) {
            $code = 500;
            $message = '数据库操作准备失败: ' . $link->error;
        } else {
            $stmt->bind_param($types, ...$ids);
            
            if ($stmt->execute()) {
                $code = 200;
                $message = 'success';
            } else {
                $code = 100;
                $message = '删除字段信息失败: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $code = 100;
        $message = '删除字段失败: ' . $stmt_field_info->error;
    }
    $stmt_field_info->close();
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);