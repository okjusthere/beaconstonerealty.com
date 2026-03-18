<?php
// 更新留言信息
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';
require_once('../../myclass/Email.php');

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$email_class = new \myclass\Email();

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 验证必要字段
if (!isset($data->id) || !is_numeric($data->id) || 
    !isset($data->update_field) || !isset($data->value)) {
    die($jsonData->jsonData(400, '缺少必要参数', []));
}

// 准备数据
$id = (int)$data->id;
$field = $data->update_field;
$value = $data->value;
$email = isset($data->email) ? $data->email : '';

$code = 500;  // 响应码
$message = '更新失败！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 验证字段名安全性（防止SQL注入）
$allowedFields = ['reply', 'state']; // 允许更新的字段
if (!in_array($field, $allowedFields)) {
    die($jsonData->jsonData(400, '不允许更新的字段', []));
}

// 如果是回复字段，先发送邮件
$send_state = true;
if ($field == 'reply' && !empty($email)) {
    $send_state = $email_class::sendinfo($email, '留言回复', $value);
}

if ($id > 0 && !empty($value) && $send_state) {
    // 使用预处理语句更新数据
    $sql = "UPDATE message SET `{$field}` = ? WHERE id = ?";
    $stmt = $link->prepare($sql);

    if ($stmt === false) {
        $message = '数据库预处理失败: ' . $link->error;
    } else {
        $stmt->bind_param("si", $value, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $code = 200;
                $message = 'success';
            } else {
                $code = 404;
                $message = '未找到要更新的记录或数据未更改';
            }
        } else {
            $code = 100;
            $message = 'error';
        }
        
        $stmt->close();
    }
} else {
    $message = '回复失败，请联系管理员';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);