<?php
//更新文章分类信息
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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

$value = $data->value; //要更新的字段值
$id = (int)$data->id; //分类ID

$code = 500;  //响应码
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("pic_position_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 使用预处理语句
$sql = "UPDATE pic_position SET name = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param('si', $value, $id);
    
    // 执行预处理语句
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 100;
        $message = 'error';
    }
} else {
    $code = 100;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);