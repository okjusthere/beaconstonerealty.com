<?php
//新增管理员信息

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
if (!my_power("user_power_add")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要参数
if (!isset($data->sort) || !isset($data->powername) || !isset($data->description)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备数据
$sort = (int)$data->sort;
$powername = trim($data->powername);
$description = trim($data->description);

// 检查角色名称是否已存在（使用预处理语句）
$sql_have = "SELECT id FROM user_power WHERE powername = ?";
$stmt_have = $link->prepare($sql_have);

if ($stmt_have) {
    $stmt_have->bind_param("s", $powername);
    $stmt_have->execute();
    $res_have = $stmt_have->get_result();
    
    if ($res_have->num_rows > 0) {
        $code = 101;
        $message = '该角色名称已存在！';
        $stmt_have->close();
        mysqli_close($link);
        echo $jsonData->jsonData($code, $message, $obj);
        die();
    }
    $stmt_have->close();
}

// 使用预处理语句插入新角色
$sql = "INSERT INTO user_power (sort, powername, description) VALUES (?, ?, ?)";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("iss", $sort, $powername, $description);
    $result = $stmt->execute();
    
    if ($result) {
        $code = 200;
        $message = 'success';
        $r_id = $stmt->insert_id; // 获取最后插入的ID
        updatelogs("用户管理，添加用户角色，ID：" . $r_id); //记录操作日志
    } else {
        $code = 100;
        $message = '添加失败，请重试！';
    }
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);