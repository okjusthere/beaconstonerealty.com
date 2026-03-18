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
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("news_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
$id = isset($data->id) ? (int)$data->id : 0;

if ($id <= 0) {
    $code = 400;
    $message = '无效的文章ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句
$sql = "UPDATE news SET is_delete = 1 WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库操作准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数并执行
$stmt->bind_param('i', $id);
$result = $stmt->execute();

if ($result) {
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("文章管理，文章放入回收站，ID：" . $id); //记录操作日志
    } else {
        $code = 404;
        $message = '未找到要操作的文章';
    }
} else {
    $code = 500;
    $message = '操作执行失败';
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);