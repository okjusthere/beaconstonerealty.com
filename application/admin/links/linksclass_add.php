<?php
// 添加文章分类信息
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

// 获取并解析输入数据
$data = json_decode(file_get_contents('php://input'));
if (json_last_error() !== JSON_ERROR_NONE) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 验证必要字段
if (!isset($data->title) || empty(trim($data->title))) {
    die($jsonData->jsonData(400, '分类名称不能为空', []));
}

// 准备数据
$sort = isset($data->sort) ? (int)$data->sort : 0;
$parentid = isset($data->parentid) ? (int)$data->parentid : 0;
$title = trim($data->title);
$thumbnail = isset($data->thumbnail) && is_array($data->thumbnail) && count($data->thumbnail) > 0 
    ? json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) 
    : '';
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("linksclass_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    die();
}

// 使用预处理语句
$sql = "INSERT INTO tb_links_class (parentid, title, thumbnail, is_show, sort) VALUES (?, ?, ?, ?, ?)";
$stmt = $link->prepare($sql);

if ($stmt === false) {
    $code = 500;
    $message = '数据库预处理失败: ' . $link->error;
} else {
    $stmt->bind_param("issii", $parentid, $title, $thumbnail, $is_show, $sort);
    
    if ($stmt->execute()) {
        $code = 200;
        $message = 'success';
        $r_id = $stmt->insert_id; // 获取插入的ID
        updatelogs("友情链接，添加友链分类，ID：" . $r_id); // 记录操作日志
    } else {
        $code = 100;
        $message = '数据库操作失败: ' . $stmt->error;
    }
    
    $stmt->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);