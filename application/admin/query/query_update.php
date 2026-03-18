<?php
//更新文章信息
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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 初始化响应数据
$code = 500;  //响应码
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("query_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要参数
if (!isset($data->value) || !isset($data->id) || !isset($data->update_field)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 安全过滤参数
$id = (int)$data->id;
$field = $data->update_field;
$value = $data->value;

// 验证字段名合法性（防止SQL注入）
$allowed_fields = ['title', 'content', 'status', 'q_condition', 'thumbnail']; // 允许更新的字段白名单
if (!in_array($field, $allowed_fields)) {
    $code = 400;
    $message = '不允许更新该字段！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 特殊字段处理
if ($field === 'thumbnail' && is_array($value)) {
    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
}

// 使用预处理语句更新数据
$sql = "UPDATE query SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $value, $id);
    $result = $stmt->execute();
    
    if ($result) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 100;
        $message = '出错了，请联系管理员！';
    }
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);