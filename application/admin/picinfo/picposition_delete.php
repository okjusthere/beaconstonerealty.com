<?php
//删除图片位置
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
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("pic_position_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
$id = (int)$data->id;

// 检查是否有子分类（使用预处理语句）
$checkSql = 'SELECT id FROM pic_info WHERE classid = ?';
$checkStmt = $link->prepare($checkSql);

if ($checkStmt) {
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkStmt->close();

    if ($checkResult->num_rows > 0) {
        $code = 100;  //响应码
        $message = '请先删除该位置下的图片';  //响应信息
        echo $jsonData->jsonData($code, $message, $obj);
    } else {
        // 使用预处理语句执行删除
        $deleteSql = 'DELETE FROM pic_position WHERE id = ?';
        $deleteStmt = $link->prepare($deleteSql);
        
        if ($deleteStmt) {
            $deleteStmt->bind_param('i', $id);
            $deleteResult = $deleteStmt->execute();
            $deleteStmt->close();
            
            if ($deleteResult) {
                updatelogs("图片管理，删除图片位置，ID：" . $id); //记录操作日志
                echo $jsonData->jsonSuccessData($obj);
            } else {
                echo $jsonData->jsonData($code, $message, $obj);
            }
        } else {
            echo $jsonData->jsonData($code, '数据库预处理失败', $obj);
        }
    }
    
    // 释放结果集
    if (isset($checkResult)) {
        $checkResult->free();
    }
} else {
    echo $jsonData->jsonData($code, '数据库预处理失败', $obj);
}

//关闭数据库链接
mysqli_close($link);