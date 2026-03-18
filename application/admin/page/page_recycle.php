<?php
//按钮 文章放入回收站
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '删除失败，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("page_delete")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$data = file_get_contents('php://input');
$data = json_decode($data);
$id = isset($data->id) ? (int)$data->id : 0;

// 验证ID有效性
if ($id <= 0) {
    $code = 400;
    $message = '无效的页面ID';
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
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result && $affected_rows > 0) {
    updatelogs("页面管理，页面放入回收站，ID：" . $id);
    echo $jsonData->jsonSuccessData($obj);
} else {
    $code = ($affected_rows === 0) ? 404 : 500;
    $message = ($affected_rows === 0) ? '未找到要操作的页面' : '操作执行失败';
    echo $jsonData->jsonData($code, $message, $obj);
}

// 关闭数据库连接
mysqli_close($link);