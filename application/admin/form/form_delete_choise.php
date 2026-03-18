<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 默认响应码
$message = '删除失败，请重试！';  // 默认响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_delete")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit;
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', $obj);
    exit;
}

$data = json_decode($input);
if ($data === null || !is_array($data) || empty($data)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少ID参数', $obj);
    exit;
}

// 验证并处理ID数组
$ids = array();
foreach ($data as $id) {
    $id = (int)$id;
    if ($id > 0) {
        $ids[] = $id;
    }
}

if (empty($ids)) {
    echo $jsonData->jsonData(400, '没有有效的表单ID', $obj);
    exit;
}

// 创建预处理语句中的占位符
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "DELETE FROM tb_form WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
    exit;
}

// 动态绑定参数
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);

$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result && $affected_rows > 0) {
    $code = 200;
    $message = 'success';
    updatelogs("在线表单，批量删除表单，ID：" . implode(',', $ids));
} else {
    $code = 500;
    $message = '删除失败，没有记录被删除';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);