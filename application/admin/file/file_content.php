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
$message = '未响应，请重试！';  // 默认响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("file_list")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit();
}

// 安全获取并过滤目录参数
$directory = isset($_GET['directory']) ? filter_var($_GET['directory'], FILTER_SANITIZE_STRING) : '';
if (empty($directory)) {
    echo $jsonData->jsonData(400, '目录参数不能为空', $obj);
    exit();
}

// 验证目录路径是否合法
if (!preg_match('/^[a-zA-Z0-9_\/\-\.]+$/', $directory)) {
    echo $jsonData->jsonData(400, '目录路径包含非法字符', $obj);
    exit();
}

// 检查文件是否存在
if (!file_exists($directory)) {
    echo $jsonData->jsonData(404, '指定的文件不存在', $obj);
    exit();
}

// 检查文件是否可读
if (!is_readable($directory)) {
    echo $jsonData->jsonData(403, '文件不可读，权限不足', $obj);
    exit();
}

// 读取文件内容
$lines = file($directory, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($lines === false) {
    echo $jsonData->jsonData(500, '文件读取失败', $obj);
    exit();
}

if (!empty($lines)) {
    $code = 200;
    $message = 'success';
    $obj["data"] = $lines;
}

// 关闭数据库链接
if ($link) {
    mysqli_close($link);
}

echo $jsonData->jsonData($code, $message, $obj);