<?php
// 添加文章信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;

include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

header('Content-Type:application/json; charset=utf-8');

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->sort, $data->classid, $data->title, $data->url, $data->is_top, $data->is_show, $data->thumbnail)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少必要参数', []);
    exit;
}

// 处理输入数据
$sort = (int)$data->sort;
$classid = !empty($data->classid) ? json_encode($data->classid) : '';
$title = $data->title; // 移除了不安全的addslashes()
$url = $data->url;
$is_top = $data->is_top ? 2 : 1;
$is_show = (int)$data->is_show;
$thumbnail = !empty($data->thumbnail) ? json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';

// 权限检查（已注释，按需启用）
if (!my_power("links_add")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', []);
    exit;
}

// 使用预处理语句插入数据
$sql = "INSERT INTO tb_links (classid, title, url, thumbnail, is_show, is_top, sort) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败: ' . $link->error, []);
    exit;
}

$stmt->bind_param("ssssiii", $classid, $title, $url, $thumbnail, $is_show, $is_top, $sort);
$result = $stmt->execute();

if ($result) {
    $r_id = $stmt->insert_id;
    $code = 200;
    $message = 'success';
    updatelogs("友情链接，添加友链，ID：" . $r_id);
} else {
    $code = 500;
    $message = 'error';
}

$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, []);