<?php
// 更新文章信息
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

// 获取并解析输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 验证必要字段
if (!isset($data->id) || !isset($data->field_name) || !isset($data->value)) {
    die($jsonData->jsonData(400, '缺少必要参数', []));
}

// 准备数据
$id = (int)$data->id;
$field = $data->field_name;
$value = $data->value;

// 处理特殊字段
if ($field == "allow_access") {
    $value = is_array($value) && count($value) > 0 ? json_encode($value) : '';
} else {
    $value = is_string($value) ? trim($value) : $value;
}

$code = 500;  // 响应码
$message = '更新失败！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("links_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    die();
}

// 使用预处理语句
$sql = "UPDATE tb_links SET `{$field}` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt === false) {
    $code = 500;
    $message = '数据库预处理失败: ' . $link->error;
} else {
    // 根据值的类型绑定参数
    if ($field == "allow_access") {
        $stmt->bind_param("si", $value, $id);
    } else if (is_int($value)) {
        $stmt->bind_param("ii", $value, $id);
    } else {
        $stmt->bind_param("si", $value, $id);
    }
    
    if ($stmt->execute()) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 100;
        $message = '出错了，请联系管理员！错误信息: ' . $stmt->error;
    }
    
    $stmt->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);