<?php
//复选框删除管理员

//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("admin_delete")) {
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

// 创建预处理语句
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "DELETE FROM admin WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库操作准备失败: ' . $link->error, []);
    exit;
}

// 动态绑定参数
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);

// 执行删除
if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    if ($affectedRows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("账号管理，批量删除账号，ID：" . implode(',', $ids));
    } else {
        $code = 404;
        $message = '未找到匹配的记录';
    }
} else {
    $message = '删除失败: ' . $stmt->error;
}

$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);