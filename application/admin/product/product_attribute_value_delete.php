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
$id = (int)$data->id; // 强制转换为整型

// 使用预处理语句
$sql = 'DELETE FROM tb_product_attribute_value WHERE id=?';
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param('i', $id);
    // 执行删除
    $execute_success = $stmt->execute();
    
    if ($execute_success) {
        $code = 200;
        $message = 'success';
        // 获取影响的行数
        $affected_rows = $stmt->affected_rows;
        if ($affected_rows === 0) {
            $code = 404;
            $message = '未找到要删除的记录';
        }
    } else {
        $code = 100;
        $message = '删除操作执行失败';
    }
    
    // 关闭预处理语句
    $stmt->close();
} else {
    $code = 100;
    $message = 'SQL预处理失败';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);