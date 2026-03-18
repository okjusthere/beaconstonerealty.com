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

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$proid = isset($_GET['proid']) ? (int)$_GET['proid'] : 0; // 产品记录id，安全过滤

// 使用预处理语句
$sql = "SELECT * FROM tb_product_attribute WHERE product_id=? ORDER BY sort DESC, id ASC";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param('i', $proid);
    // 执行查询
    $execute_success = $stmt->execute();
    
    if ($execute_success) {
        // 获取结果集
        $result = $stmt->get_result();
        $code = 200;
        $message = '查询成功！';
        
        // 处理查询结果
        while ($row = $result->fetch_assoc()) {
            $row["attribute_value_id"] = json_decode($row["attribute_value_id"]);
            $row["attribute_value"] = json_decode($row["attribute_value"]);
            $row["photo_album"] = empty($row["photo_album"]) ? [] : json_decode($row["photo_album"]);
            $row["price"] = (float)$row["price"];
            $row["inventory"] = (int)$row["inventory"];
            $row["sort"] = (int)$row["sort"];
            
            $obj["data"][] = $row;
        }
        
        // 释放结果集
        $result->free();
    } else {
        $code = 100;
        $message = '查询执行失败';
    }
    
    // 关闭预处理语句
    $stmt->close();
} else {
    $code = 100;
    $message = 'SQL预处理失败';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);