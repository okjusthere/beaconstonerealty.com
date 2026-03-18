<?php
//复选框 文章放入回收站
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
$message = '未响应！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("query_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 验证输入数据
if (!is_array($data) || empty($data)) {
    $code = 400;  //响应码
    $message = '请选择要操作的文章！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 过滤和验证ID数组
$ids = array_filter($data, 'is_numeric');
if (empty($ids)) {
    $code = 400;  //响应码
    $message = '无效的文章ID参数！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备IN语句的参数占位符
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// 使用预处理语句批量更新
$sql = "UPDATE query SET is_delete = 1 WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $result = $stmt->execute();
    
    if ($result) {
        echo $jsonData->jsonSuccessData($obj);
    } else {
        $code = 100;
        $message = '操作失败，请重试或联系管理员！';
        echo $jsonData->jsonData($code, $message, $obj);
    }
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
    echo $jsonData->jsonData($code, $message, $obj);
}

//关闭数据库链接
mysqli_close($link);