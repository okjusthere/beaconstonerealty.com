<?php
//添加图片
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input');
$data = json_decode($data);

// 初始化变量
$paixu = isset($data->paixu) ? (int)$data->paixu : 0;
$classid = isset($data->classid) ? (int)$data->classid : 0;
$path = isset($data->path) && count($data->path) > 0 ? json_encode($data->path, JSON_UNESCAPED_UNICODE) : '';
$name = isset($data->name) ? $data->name : '';
$url = isset($data->url) ? $data->url : '';
$remarks = isset($data->remarks) ? $data->remarks : '';
$width = isset($data->width) ? (int)$data->width : 0;
$height = isset($data->height) ? (int)$data->height : 0;

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("pic_add")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句
$sql = "INSERT INTO pic_info (classid, path, name, url, remarks, width, height, paixu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库操作准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
$stmt->bind_param("issssiii", 
    $classid, 
    $path, 
    $name, 
    $url, 
    $remarks, 
    $width, 
    $height, 
    $paixu
);

// 执行插入
$result = $stmt->execute();
$new_id = $stmt->insert_id;
$stmt->close();

if ($result) {
    $code = 200;
    $message = 'success';
    updatelogs("图片管理，添加图片，ID：" . $new_id);
    echo $jsonData->jsonSuccessData($obj);
} else {
    $code = 500;
    $message = '添加图片失败';
    echo $jsonData->jsonData($code, $message, $obj);
}

// 关闭数据库连接
mysqli_close($link);