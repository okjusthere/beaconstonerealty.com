<?php
//按钮 文章放入回收站
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

// 验证ID参数
if (!isset($data->id) || !is_numeric($data->id)) {
    $code = 400;  //响应码
    $message = '无效的文章ID！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$id = (int)$data->id;

// 使用预处理语句更新数据
$sql = "UPDATE query SET is_delete = 1 WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    
    if ($result) {
        // 使用专门的success方法返回成功响应
        echo $jsonData->jsonSuccessData($obj);
    } else {
        $code = 100;
        $message = '删除失败，请重试，或联系管理员！';
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