<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';
require_once('../../vendor/autoload.php');
require_once('../../myclass/Excel.php'); // 引用Excel导出类

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("message_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit();
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo $jsonData->jsonData(400, '无效的请求数据', $obj);
    exit();
}

// 验证ID数组
$ids = array_filter($data, 'is_numeric');
if (empty($ids)) {
    echo $jsonData->jsonData(400, '无效的ID参数', $obj);
    exit();
}

// 使用预处理语句查询数据
$sql = "SELECT * FROM message WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ") ORDER BY id DESC";
$stmt = $link->prepare($sql);

if ($stmt === false) {
    echo $jsonData->jsonData(500, '数据库预处理失败: ' . $link->error, $obj);
    exit();
}

// 绑定参数
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);

if (!$stmt->execute()) {
    echo $jsonData->jsonData(500, '数据库查询失败: ' . $stmt->error, $obj);
    $stmt->close();
    exit();
}

$result = $stmt->get_result();
$data_export = [];

while ($row = $result->fetch_assoc()) {
    $row['state'] = $row['state'] == '1' ? '未回复' : '已回复';
    $data_export[] = $row;
}

$stmt->close();

// 导出Excel
if (count($data_export) > 0) {
    $excel = new \myclass\Excel();

    $header = [
        ['column' => 'A', 'name' => '编号', 'type' => 'autoincrement', 'iscenter' => '1'],
        ['column' => 'B', 'name' => '咨询主题', 'key' => 'title', 'width' => '30'],
        ['column' => 'C', 'name' => '联系人', 'key' => 'name', 'iscenter' => '1'],
        ['column' => 'D', 'name' => '手机号', 'key' => 'phone', 'iscenter' => '1'],
        ['column' => 'E', 'name' => '邮箱', 'key' => 'email', 'width' => '20', 'iscenter' => '1'],
        ['column' => 'F', 'name' => '咨询内容', 'key' => 'message', 'width' => '30'],
        ['column' => 'G', 'name' => '回复状态', 'key' => 'state', 'width' => '13', 'iscenter' => '1'],
        ['column' => 'H', 'name' => '回复内容', 'key' => 'reply', 'width' => '30'],
        ['column' => 'I', 'name' => '提交时间', 'key' => 'add_time', 'width' => '20', 'iscenter' => '1', 'type' => 'timestamp'],
        ['column' => 'J', 'name' => 'IP地址', 'key' => 'ip', 'width' => '20', 'iscenter' => '1'],
    ];

    $filename = "留言信息";
    if ($excel::dataToExcel($header, $data_export, $filename)) {
        $code = 200;
        $message = 'success';
        $obj['name'] = $filename . '.xlsx';
        $obj['directory'] = '../../media_library/download/' . $obj['name'];
    } else {
        $code = 100;
        $message = 'Excel导出失败';
    }
} else {
    $code = 100;
    $message = '没有可导出的数据';
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);