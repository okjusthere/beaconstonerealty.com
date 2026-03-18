<?php
//修改路由模板信息
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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 初始化变量
$id = $data->id ?? ''; //模板ID
$template_name = $data->template_name ?? ''; //模板名称
$route_page = $data->route_page ?? ''; //路由页面
$position_key = $data->position_key ?? ''; //引用位置

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

//判断后台用户权限
if (!my_power("route_template_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必填字段
if (empty($template_name) || empty($route_page) || empty($position_key)) {
    $code = 400;
    $message = '模板名称、路由页面和引用位置不能为空！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 检查pattern是否已存在
$check_sql = "SELECT id FROM tb_route_template WHERE (template_name = ? or route_page=?) AND id<>? LIMIT 1";
$check_stmt = $link->prepare($check_sql);
if (!$check_stmt) {
    $code = 500;
    $message = '数据库准备失败！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$check_stmt->bind_param("ssi", $template_name,$route_page,$id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $code = 409;  // 409 Conflict 表示资源冲突
    $message = '模板名称/路由页面已存在，请勿重复添加！';
    $check_stmt->close();
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}
$check_stmt->close();

// 使用预处理语句插入主表数据
$sql = "UPDATE tb_route_template SET template_name=?,route_page=?,position_key=? WHERE id=?";

$stmt = $link->prepare($sql);
if (!$stmt) {
    $code = 500;
    $message = '数据库准备失败！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
$stmt->bind_param("sssi", $template_name, $route_page, $position_key, $id);

// 执行修改
$result = $stmt->execute();

if ($result) {
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 401;
        $message = '数据未发生变化，无需更新';
    }
} else {
    $code = 500;
    $message = '更新操作执行失败，请联系管理员';
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);