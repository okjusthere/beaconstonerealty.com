<?php
// 删除文章
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;

include_once '../../../myclass/ResponseJson.php';

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 默认响应码
$message = '删除失败，请重试！';  // 默认响应信息
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', $obj);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少ID参数', $obj);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的友链ID', $obj);
    exit;
}

// 权限检查（已注释，按需启用）
 if (!my_power("links_delete")) {
     echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
     exit;
 }

// 使用预处理语句删除数据
$sql = "DELETE FROM tb_links WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
    exit;
}

$stmt->bind_param("i", $id);
$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result) {
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("友情链接，删除友链，ID：" . $id);
    } else {
        $code = 200;
        $message = 'success';
    }
} else {
    $code = 500;
    $message = '友链删除失败';
}

mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);