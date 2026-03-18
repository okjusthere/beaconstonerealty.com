<?php
//编辑管理员信息

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

// 初始化响应数据
$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("user_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要参数
if (!isset($data->id) || !isset($data->power) || !isset($data->username) || 
    !isset($data->usermobile) || !isset($data->state)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备数据
$id = (int)$data->id;
$power = (int)$data->power;
$username = trim($data->username);
$usermobile = trim($data->usermobile);
$remarks = isset($data->remarks) ? trim($data->remarks) : '';
$state = (int)$data->state;

// 处理密码（如果有）
$password = '';
if (!empty($data->password)) {
    $password = my_crypt(trim($data->password), 1);
}

// 构建SQL更新语句
$sql = "UPDATE user SET 
        power = ?, 
        user_name = ?, 
        user_mobile = ?, 
        " . (!empty($password) ? "user_password = ?, " : "") . "
        remarks = ?, 
        state = ? 
        WHERE id = ?";

// 使用预处理语句更新用户信息
$stmt = $link->prepare($sql);

if ($stmt) {
    // 动态绑定参数
    if (!empty($password)) {
        $stmt->bind_param("issssii", 
            $power, $username, $usermobile, 
            $password, $remarks, $state, $id);
    } else {
        $stmt->bind_param("isssii", 
            $power, $username, $usermobile, 
            $remarks, $state, $id);
    }
    
    $result = $stmt->execute();
    
    if ($result) {
        $code = 200;
        $message = '更新成功';
    } else {
        $code = 100;
        $message = '更新失败，请重试！';
    }
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);