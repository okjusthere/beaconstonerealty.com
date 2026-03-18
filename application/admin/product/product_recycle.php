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

$data = file_get_contents('php://input'); // 获取非表单数据
$data = json_decode($data); // 解码axios传递过来的json数据
$id = isset($data->id) ? (int)$data->id : 0; // 安全过滤ID参数

if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的产品ID', $obj);
    exit;
}

// 使用预处理语句进行逻辑删除
$sql = "UPDATE product SET is_delete=1 WHERE id=?";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param('i', $id);
    // 执行更新
    $execute_success = $stmt->execute();
    
    if ($execute_success) {
        $affected_rows = $stmt->affected_rows;
        
        if ($affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("产品管理，产品放入回收站，ID：" . $id);
        } else {
            $code = 404;
            $message = '未找到要删除的产品';
        }
    } else {
        $code = 100;
        $message = '删除操作执行失败: ' . $stmt->error;
    }
    
    // 关闭预处理语句
    $stmt->close();
} else {
    $code = 100;
    $message = 'SQL预处理失败: ' . $link->error;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);