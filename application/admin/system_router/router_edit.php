<?php
//修改后台路由
//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

header('Content-Type:application/json; charset=utf-8');

 if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
 if (empty($_SESSION["manager_username"]) && my_crypt($_SESSION["manager_username"], 2) != '13812345678') {
     echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
     exit;
 }

$code = 500;  //响应码
$message = '未响应';  //响应信息

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}

// 提取并过滤数据
$id = $data->id ?? 0;
$parent_id = isset($data->parent_id) ? intval($data->parent_id) : 0;
$router_level = isset($data->router_level) ? intval($data->router_level) : 0;
$router_path = isset($data->router_path) ? trim($data->router_path) : '';
$router_name = isset($data->router_name) ? trim($data->router_name) : '';
$component = isset($data->component) ? trim($data->component) : '';
$router_icon = isset($data->router_icon) ? trim($data->router_icon) : '';
$hidden = isset($data->hidden) ? intval($data->hidden) : 1;
$sort = isset($data->sort) ? intval($data->sort) : 0;

// 验证必要字段
if (($id <= 0) || empty($router_path) || empty($router_name) || empty($component)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再保存', []);
    exit;
}

try {
    $sql = "update tb_system_router set parent_id=?,router_level=?,router_path=?,router_name=?,component=?,router_icon=?,hidden=?,sort=? where id=?";
    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('修改路由语句准备失败：' . $link->error);
    $stmt->bind_param("iisssssii", $parent_id, $router_level, $router_path, $router_name, $component, $router_icon, $hidden, $sort, $id);
    if (!$stmt->execute()) throw new Exception('修改路由出错：' . $stmt->error);
    $code = 200;  //响应码
    $message = 'success';  //响应信息
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message);
