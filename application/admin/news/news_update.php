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

// 验证和初始化变量
$id = isset($data->id) ? (int)$data->id : 0;
$field = isset($data->field_name) ? $data->field_name : '';
$value = isset($data->value) ? $data->value : '';

// 处理allow_access字段
if ($field === "allow_access") {
    $value = (is_array($value) || is_object($value)) && count((array)$value) > 0 
        ? json_encode($value) 
        : '';
} else {
    // 其他字段不做addslashes处理，预处理语句会自动处理
    $value = is_scalar($value) ? $value : '';
}

$code = 500;  //响应码
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("news_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证输入
if ($id <= 0) {
    $code = 400;
    $message = '无效的文章ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句
$sql = "UPDATE news SET `$field` = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库操作准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$fieldType = ($field == "paixu") ? 'i' : 's'; // 可根据实际字段类型调整

// 绑定参数并执行
$stmt->bind_param($fieldType."i", $value, $id);
$result = $stmt->execute();

if ($result) {
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 401;
        $message = '数据未发生变化，无需更新';
    }
} else {
    $code = 500;
    $message = '更新操作执行失败';
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);