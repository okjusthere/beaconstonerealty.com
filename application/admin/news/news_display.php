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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

$field = "show_type"; //要更新的字段-文章列表样式
$id = isset($data->id) ? (int)$data->id : 0; //分类ID
$value = isset($data->type) ? $data->type : 1; //要更新的字段值

$code = 500;  //响应码
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("proclass_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 准备SQL语句
if ($id > 0) {
    $sql = "UPDATE news_class SET {$field} = ? WHERE id = ? AND is_delete = 0";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("si", $value, $id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $code = 200;
            $message = 'success';
        } else {
            $code = 100;
            $message = 'error';
        }
    } else {
        $code = 500;
        $message = '数据库操作准备失败';
    }
} else {
    $sql = "UPDATE news_class SET {$field} = ?";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $value);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $code = 200;
            $message = 'success';
        } else {
            $code = 100;
            $message = 'error';
        }
    } else {
        $code = 500;
        $message = '数据库操作准备失败';
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);