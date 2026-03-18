<?php
//获取管理员表信息

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

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array("data" => array()); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("user_power_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 构建SQL查询
$sql = "SELECT * FROM user_power";
$params = array();
$types = "";

// 添加搜索条件
if (!empty($keywords)) {
    $sql .= " WHERE powername LIKE ?";
    $params[] = "%{$keywords}%";
    $types .= "s";
}

$sql .= " ORDER BY sort DESC, id ASC";

// 使用预处理语句执行查询
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数（如果有）
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res) {
        $code = 200;
        $message = 'success';
        
        while ($row = $res->fetch_assoc()) {
            $obj["data"][] = $row;
        }
    }
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);