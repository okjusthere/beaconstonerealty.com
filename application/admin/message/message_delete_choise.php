<?php
// 批量删除留言信息
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
if (!my_power("message_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit();
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    echo $jsonData->jsonData(400, '无效的请求数据', $obj);
    exit();
}

// 验证ID数组
$ids = array_filter($data, 'is_numeric');
if (empty($ids)) {
    echo $jsonData->jsonData(400, '无效的ID参数', $obj);
    exit();
}

// 准备ID列表用于日志记录
$id_list = implode(',', $ids);

// 创建预处理语句 - 删除留言
$sql = "DELETE FROM message WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
$stmt = $link->prepare($sql);

if ($stmt === false) {
    $message = '数据库预处理失败: ' . $link->error;
} else {
    // 绑定参数
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("管理中心，批量删除客户留言，ID：" . $id_list);
        } else {
            $code = 404;
            $message = '未找到要删除的记录';
        }
    } else {
        $code = 100;
        $message = '删除失败: ' . $stmt->error;
    }
    
    $stmt->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);