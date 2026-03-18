<?php
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
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 查询有多少种产品展现方式
$sql = "SELECT COUNT(DISTINCT show_type) AS number FROM news_class WHERE is_delete = 0";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    
    if ($res) {
        $row = $res->fetch_assoc();
        $number = (int)$row["number"];
        
        if ($number == 1) {
            // 获取当前产品列表展现状态
            $sql_type = "SELECT show_type FROM news_class WHERE is_delete = 0 LIMIT 1";
            $stmt_type = $link->prepare($sql_type);
            
            if ($stmt_type) {
                $stmt_type->execute();
                $res_type = $stmt_type->get_result();
                $stmt_type->close();
                
                if ($res_type) {
                    $row_type = $res_type->fetch_assoc();
                    $code = 200;
                    $message = 'success';
                    $obj["data"] = (int)$row_type["show_type"];
                }
            }
        } else {
            $code = 200;
            $message = 'success';
            $obj["data"] = -1;
        }
    }
} else {
    $code = 500;
    $message = '数据库查询准备失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);