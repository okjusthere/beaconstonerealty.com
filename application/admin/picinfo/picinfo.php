<?php
//获取图片表信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("pic_list")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用预处理语句
$sql = 'SELECT * FROM pic_info ORDER BY paixu DESC, id DESC';
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 执行查询
$stmt->execute();
$res = $stmt->get_result();

if ($res) {
    $code = 200;
    $message = '查询成功';
    $obj["data"] = array();
    
    while ($row = $res->fetch_assoc()) {
        $row["path"] = json_decode($row["path"]);
        $obj["data"][] = $row;
    }
    
    $stmt->close();
} else {
    $code = 500;
    $message = '查询执行失败';
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);