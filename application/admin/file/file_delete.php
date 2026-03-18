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
if (!my_power("file_delete")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit();
}

// 获取并验证输入数据
$data = file_get_contents('php://input');
if ($data === false) {
    echo $jsonData->jsonData(400, '无法读取输入数据', $obj);
    exit();
}

$data = json_decode($data);
if ($data === null) {
    echo $jsonData->jsonData(400, '无效的JSON数据格式', $obj);
    exit();
}

// 验证目录参数
if (!isset($data->directory) || !is_string($data->directory)) {
    echo $jsonData->jsonData(400, '缺少或无效的文件路径参数', $obj);
    exit();
}

$directory = $data->directory;

// 安全验证文件路径
if (!preg_match('/^[a-zA-Z0-9_\/\-\.]+$/', $directory)) {
    echo $jsonData->jsonData(400, '文件路径包含非法字符', $obj);
    exit();
}

// 检查文件是否存在
if (!file_exists($directory)) {
    echo $jsonData->jsonData(404, '要删除的文件不存在', $obj);
    exit();
}

// 检查是否是文件（防止删除目录）
if (!is_file($directory)) {
    echo $jsonData->jsonData(400, '指定的路径不是文件', $obj);
    exit();
}

// 检查文件是否可写
if (!is_writable($directory)) {
    echo $jsonData->jsonData(403, '文件不可写，删除失败', $obj);
    exit();
}

// 执行删除操作
if (unlink($directory)) {
    $code = 200;
    $message = 'success';
    updatelogs("删除文件，文件名：" . $directory);
} else {
    echo $jsonData->jsonData(500, 'error', $obj);
    exit();
}

// 关闭数据库链接
if ($link) {
    mysqli_close($link);
}

// 成功响应
echo $jsonData->jsonData($code, $message, $obj);