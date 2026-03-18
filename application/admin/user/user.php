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
$obj = array("data" => array()); //初始化返回对象

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("user_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 构建SQL查询
$sql = "SELECT * FROM user";
$params = array();
$types = "";

// 添加搜索条件
if (!empty($keywords)) {
    $sql .= " WHERE user_mobile LIKE ? OR user_name LIKE ?";
    $searchTerm = "%{$keywords}%";
    $params = array($searchTerm, $searchTerm);
    $types = "ss";
}

$sql .= " ORDER BY id DESC";

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
            // 解密用户密码
            if (!empty($row["user_password"])) {
                $row["user_password"] = my_crypt($row["user_password"], 2);
            }
            // 获取用户角色名（使用预处理语句）
            $row["powername"] = getPowerName($row["power"]);
            $obj["data"][] = $row;
        }
    }
    $stmt->close();
}

/**
 * 获取角色名称
 * @param int $id 管理员角色ID
 * @return string 角色名称
 */
function getPowerName($id) {
    global $link;
    $power_name = "";
    
    $sql = "SELECT powername FROM user_power WHERE id = ?";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $res->num_rows > 0) {
            $power_name = $res->fetch_assoc()["powername"];
        }
        $stmt->close();
    }
    
    return $power_name;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);