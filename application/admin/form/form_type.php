<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 默认响应码
$message = '未响应，请重试！';  // 默认响应信息
$obj = ['data' => []];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_list")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit;
}

// 获取并验证搜索关键词
$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 准备SQL语句
$baseSql = "SELECT *,(SELECT COUNT(id) FROM tb_form WHERE type_id=tb_form_type.id) AS number FROM tb_form_type";
$orderBy = " ORDER BY state ASC, id DESC";

// 使用预处理语句处理关键词搜索
if (!empty($keywords)) {
    $sql = $baseSql . " WHERE title LIKE ?" . $orderBy;
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
        exit;
    }
    
    $searchParam = "%{$keywords}%";
    $stmt->bind_param("s", $searchParam);
} else {
    $sql = $baseSql . $orderBy;
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
        exit;
    }
}

// 执行查询
if (!$stmt->execute()) {
    $stmt->close();
    echo $jsonData->jsonData(500, '数据库查询失败', $obj);
    exit;
}

$result = $stmt->get_result();

if ($result) {
    $code = 200;
    $message = '查询成功！';
    
    while ($row = $result->fetch_assoc()) {
        // 转换布尔值字段
        $row["state"] = $row["state"] == "1";
        $row["sms_notification"] = $row["sms_notification"] == "1";
        $row["email_notification"] = $row["email_notification"] == "1";
        
        // 解码JSON字段
        $row["field"] = json_decode($row["field"]);
        $row["field_show"] = json_decode($row["field_show"]);
        
        // 添加编辑控制字段
        $row["edit_title"] = false;
        
        $obj["data"][] = $row;
    }
    
    $stmt->close();
} else {
    $code = 500;
    $message = '获取查询结果失败';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);