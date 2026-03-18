<?php
//获取路由规则表信息
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
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("route_template_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit; //终止继续执行
}

$keywords = isset($_GET['keywords']) ? explode(',', trim(rawurldecode($_GET['keywords']))) : ''; //关键词

// 构建基础SQL和参数
$sql = "SELECT id,template_name,route_page,position_key FROM tb_route_template";

$types = "";
$params = [];
// 添加搜索条件
if (!empty($keywords)) {
    $sql .= " WHERE";
    foreach ($keywords as $key => $value) {
        $sql .= $key === 0 ? "" : " or";
        $sql .= " position_key=?";
        $types .= "s";
        $params[] = $value;
    }
}

// 添加排序
$sql .= " ORDER BY id ASC";

// 执行查询
$stmt = $link->prepare($sql);
if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

if (!empty($types) && !empty($params)) {
    $stmt->bind_param($types, ...$params); //参数绑定
}

// 执行查询
$stmt->execute();
$res = $stmt->get_result();

$code = 200;
$message = 'success';

$obj["data"] = array();
while ($row = $res->fetch_assoc()) {
    $row["editing"] = false;
    $obj["data"][] = $row;
}

//获取对应引用位置的路由模板
if (count($params) > 0) {
    foreach ($params as $key => $value) {
        $obj[$value] = array();
        foreach ($obj["data"] as $row) {
            if ($row['position_key'] == $value) {
                $obj[$value][] = ["value" => (string)$row["id"], "label" => $row["template_name"]];
            }
        }
    }
}

$stmt->close();

//关闭数据库链接
mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);