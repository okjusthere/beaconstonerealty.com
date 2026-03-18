<?php
//获取文章分类信息
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

//使用预处理语句查询
$sql = 'SELECT * FROM user_power ORDER BY sort DESC, id ASC';
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res) {
        $code = 200;
        $message = '查询成功！';
        
        while ($row = $res->fetch_assoc()) {
            $obj["data"][] = [
                "value" => $row["id"],
                "label" => $row["powername"]
            ];
        }
    }
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);