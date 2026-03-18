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

$data = file_get_contents('php://input');
$data = json_decode($data);

$code = 500;
$message = '更新失败！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 查询有多少种产品展现方式
$sql = "SELECT COUNT(DISTINCT show_type) AS number FROM product_class WHERE is_delete=0";
$stmt = $link->prepare($sql);

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row["number"] == 1) {
        // 获取当前产品列表展现状态
        $sql_type = "SELECT show_type FROM product_class WHERE is_delete=0 LIMIT 1";
        $stmt_type = $link->prepare($sql_type);
        
        if ($stmt_type && $stmt_type->execute()) {
            $result_type = $stmt_type->get_result();
            $row_type = $result_type->fetch_assoc();
            $stmt_type->close();
            
            $code = 200;
            $message = 'success';
            $obj["data"] = (int)$row_type["show_type"];
        } else {
            $message = '获取展现状态失败';
            if ($stmt_type) {
                $message .= ': ' . $stmt_type->error;
                $stmt_type->close();
            }
        }
    } else {
        $code = 200;
        $message = 'success';
        $obj["data"] = -1;
    }
} else {
    $message = '查询展现方式数量失败';
    if ($stmt) {
        $message .= ': ' . $stmt->error;
        $stmt->close();
    }
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);