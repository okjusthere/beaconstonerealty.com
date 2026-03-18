<?php
//复选框 文章放入回收站
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

// 验证输入数据
if (!is_array($data) || count($data) === 0) {
    $code = 400;
    $message = '请选择要操作的页面';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备ID列表和参数绑定字符串
$placeholders = implode(',', array_fill(0, count($data), '?'));
$types = str_repeat('i', count($data));

// 使用预处理语句
$sql = "UPDATE news SET is_delete = 1 WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库操作准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
$stmt->bind_param($types, ...$data);

// 执行更新
$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result) {
    if ($affected_rows > 0) {
        $id_list = implode(',', $data);
        updatelogs("页面管理，页面批量放入回收站，ID：" . $id_list);
        echo $jsonData->jsonSuccessData($obj);
    } else {
        $code = 404;
        $message = '未找到要操作的页面';
        echo $jsonData->jsonData($code, $message, $obj);
    }
} else {
    $code = 500;
    $message = '操作执行失败';
    echo $jsonData->jsonData($code, $message, $obj);
}

// 关闭数据库连接
mysqli_close($link);