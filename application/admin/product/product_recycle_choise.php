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

// 判断后台用户权限
if (!my_power("product_delete")) {
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

// 过滤ID数组，确保都是数字
$ids = array();
foreach ($data as $id) {
    $id = (int)$id;
    if ($id > 0) {
        $ids[] = $id;
    }
}

if (empty($ids)) {
    echo $jsonData->jsonData(400, '无效的产品ID', $obj);
    exit;
}

// 生成预处理语句的占位符
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "UPDATE product SET is_delete=1 WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $bind_result = $stmt->bind_param($types, ...$ids);
    
    if ($bind_result && $stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        
        if ($affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("产品管理，产品批量放入回收站，ID：" . implode(',', $ids));
        } else {
            $code = 404;
            $message = '未找到要删除的产品';
        }
    } else {
        $code = 100;
        $message = '删除操作执行失败: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $code = 100;
    $message = 'SQL预处理失败: ' . $link->error;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);