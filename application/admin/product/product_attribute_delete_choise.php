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

$code = 500;  // 响应码
$message = '删除失败，请重试！';  // 响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

$data = file_get_contents('php://input'); // 获取非表单数据
$data = json_decode($data); // 解码axios传递过来的json数据

// 验证输入数据
if (!is_array($data) || empty($data)) {
    echo $jsonData->jsonData(400, '无效的请求参数', $obj);
    exit;
}

// 开始事务处理
$link->begin_transaction();

// 生成预处理所需的占位符和类型字符串
$placeholders = implode(',', array_fill(0, count($data), '?'));
$types = str_repeat('i', count($data));

// 1. 将子分类提升为顶级分类
$sql_update = "UPDATE tb_product_attribute_class SET parentid=0 WHERE parentid IN ($placeholders)";
$stmt_update = $link->prepare($sql_update);
$success = $stmt_update && $stmt_update->bind_param($types, ...$data) && $stmt_update->execute();

if ($success) {
    $stmt_update->close();
    
    // 2. 删除属性值
    $sql_value = "DELETE FROM tb_product_attribute_value WHERE attribute_class IN ($placeholders)";
    $stmt_value = $link->prepare($sql_value);
    $success = $stmt_value && $stmt_value->bind_param($types, ...$data) && $stmt_value->execute();
    
    if ($success) {
        $stmt_value->close();
        
        // 3. 删除分类
        $sql = "DELETE FROM tb_product_attribute_class WHERE id IN ($placeholders)";
        $stmt = $link->prepare($sql);
        $success = $stmt && $stmt->bind_param($types, ...$data) && $stmt->execute();
        
        if ($success) {
            $stmt->close();
            $link->commit();  // 提交事务
            $code = 200;
            $message = 'success';
        } else {
            $message = '删除分类失败';
            if ($stmt) $stmt->close();
        }
    } else {
        $message = '删除属性值失败';
        if ($stmt_value) $stmt_value->close();
    }
} else {
    $message = '更新子分类失败';
    if ($stmt_update) $stmt_update->close();
}

// 如果有错误发生则回滚
if (!$success) {
    $link->rollback();
    $code = 100;
    // 获取具体的错误信息
    $error = $link->error;
    if (!empty($error)) {
        $message .= ': ' . $error;
    }
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);