<?php
//按钮 文章分类放入回收站
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
if (!my_power("newsclass_delete")) {
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
    $message = '无效的分类ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 1. 检查是否有子分类
$sql_check = 'SELECT id FROM news_class WHERE parentid = ?';
$stmt_check = $link->prepare($sql_check);

if (!$stmt_check) {
    $code = 500;
    $message = '数据库查询准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$stmt_check->bind_param('i', $id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
$has_children = $res_check->num_rows > 0;
$stmt_check->close();

if ($has_children) {
    $code = 100;
    $message = '请先删除该分类下的子分类';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 2. 放入回收站
$sql_update = 'UPDATE news_class SET is_delete = 1 WHERE id = ?';
$stmt_update = $link->prepare($sql_update);

if (!$stmt_update) {
    $code = 500;
    $message = '数据库更新准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$stmt_update->bind_param('i', $id);
$result = $stmt_update->execute();
$affected_rows = $stmt_update->affected_rows;
$stmt_update->close();

if ($result) {
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("文章管理，文章分类放入回收站，ID：" . $id);
    } else {
        $code = 200;
        $message = 'success';
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);