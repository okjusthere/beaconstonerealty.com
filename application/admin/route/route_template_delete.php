<?php
//删除路由信息
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
$id = $data->id ?? ''; //规则ID

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

//判断后台用户权限
if (!my_power("route_template_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必填字段
if (empty($id)) {
    $code = 400;
    $message = '删除失败，无效的ID参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句删除主表数据
$sql = 'DELETE FROM tb_route_template WHERE id = ?';

$stmt = $link->prepare($sql);
if (!$stmt) {
    $code = 500;
    $message = '数据库准备失败！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
$stmt->bind_param("i", $id);

// 执行修改
$result = $stmt->execute();

if ($result) {
    $code = 200;
    $message = 'success';
} else {
    $code = 500;
    $message = '删除操作执行失败，请联系管理员';
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);