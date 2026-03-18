<?php
//获取图片位置表信息
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
if (!my_power("pic_position_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 使用预处理语句
$sql = 'SELECT * FROM pic_position';
$stmt = $link->prepare($sql);

if ($stmt) {
    // 执行查询
    $stmt->execute();
    
    // 获取结果集
    $result = $stmt->get_result();
    
    if ($result) {
        $code = 200;
        $message = '查询成功！';
        
        while ($row = $result->fetch_assoc()) {
            $row["edit_name"] = false; //控制后台列表页，是显示文本，还是编辑框
            $obj["data"][] = $row;
        }
        
        // 释放结果集
        $result->free();
    }
    
    // 关闭预处理语句
    $stmt->close();
} else {
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);