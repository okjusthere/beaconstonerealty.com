<?php
// 编辑网站优化信息
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

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}

// 提取并过滤数据
$id = (int)$data->id;
$seo_title = isset($data->seo_title) ? trim($data->seo_title) : '';
$seo_keywords = isset($data->seo_keywords) ? trim($data->seo_keywords) : '';
$seo_description = isset($data->seo_description) ? trim($data->seo_description) : '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("webseo")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 准备预处理语句
$sql = "UPDATE web_seo SET seo_title = ?, seo_keywords = ?, seo_description = ? WHERE id = ?";

$stmt = $link->prepare($sql);
if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库更新准备失败: ' . $link->error, []);
    exit;
}

// 绑定参数
$stmt->bind_param("sssi", $seo_title, $seo_keywords, $seo_description, $id);

// 执行更新
if ($stmt->execute()) {
    $code = 200;
    $message = 'success';
    // updatelogs("基础设置，修改网站优化");
} else {
    $code = 100;
    $message = 'error';
}

// 关闭语句和连接
$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);