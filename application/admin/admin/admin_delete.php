<?php
//按钮删除管理员

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
$data = json_decode(file_get_contents('php://input'));
if (json_last_error() !== JSON_ERROR_NONE || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的ID参数', []);
    exit;
}

// 使用预处理语句删除记录
$sql = 'DELETE FROM admin WHERE id = ?';
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库操作准备失败', []);
    exit;
}

$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("账号管理，删除账号，ID：" . $id);
    } else {
        $code = 404;
        $message = '未找到要删除的记录';
    }
} else {
    $message = '删除失败: ' . $stmt->error;
}

$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);