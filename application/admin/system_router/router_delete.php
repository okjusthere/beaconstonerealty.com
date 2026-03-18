<?php
//删除后台路由
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

// 验证必要字段
if (($id <= 0)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再操作', []);
    exit;
}

try {
    // 删除前查询是否有子路由,有子路由则不删除且提醒
    $check_sql = "select id from tb_system_router where parent_id=? limit 1";
    $check_stmt = $link->prepare($check_sql);
    if (!$check_stmt) throw new Exception('查询菜单语句准备失败：' . $link->error);
    $check_stmt->bind_param("i", $id);
    if (!$check_stmt->execute()) throw new Exception('查询路由出错：' . $check_stmt->error);
    $check_res = $check_stmt->get_result();
    if ($check_res->num_rows > 0) throw new Exception('该菜单存在子菜单,无法删除');

    $sql = "delete from tb_system_router where id=?";
    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('删除菜单语句准备失败：' . $link->error);
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) throw new Exception('删除菜单出错：' . $stmt->error);
    $code = 200;  //响应码
    $message = 'success';  //响应信息
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    if (isset($check_stmt) && $check_stmt instanceof mysqli_stmt) {
        $check_stmt->close();
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message);
