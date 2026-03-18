<?php
// 更新文章分类信息
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

$data = file_get_contents('php://input'); // 获取非表单数据
$data = json_decode($data); // 解码axios传递过来的json数据

$value = $data->value; // 要更新的字段值（不再使用addslashes）
$id = (int)$data->id; // 分类ID
$field = $data->field_name; // 要更新的字段

$code = 500;  // 响应码
$message = '更新失败！';  // 响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 验证字段名是否合法（防止SQL注入）
$allowed_fields = ['name', 'description', 'sort_order']; // 根据实际情况添加允许的字段
if (!in_array($field, $allowed_fields)) {
    $code = 400;
    $message = '非法的字段名';
    echo $jsonData->jsonData($code, $message, $obj);
    exit;
}

// 使用预处理语句
$sql = "UPDATE tb_product_attribute_class SET `{$field}` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param('si', $value, $id);
    $result = $stmt->execute();
    
    if ($result) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 100;
        $message = 'error: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $code = 100;
    $message = 'error: ' . $link->error;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);