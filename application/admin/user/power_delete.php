<?php
//按钮删除管理员

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
if (!my_power("user_power_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 验证ID参数
if (!isset($data->id) || !is_numeric($data->id)) {
    $code = 400;  //响应码
    $message = '无效的角色ID！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$id = (int)$data->id;

// 使用事务确保数据一致性
$link->begin_transaction();

// 使用预处理语句删除角色
$sql = "DELETE FROM user_power WHERE id = ?";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        // 使用预处理语句删除关联用户
        $sql_del_admin = "DELETE FROM user WHERE power = ?";
        $stmt_del_admin = $link->prepare($sql_del_admin);
        
        if ($stmt_del_admin) {
            $stmt_del_admin->bind_param("i", $id);
            $result_del_admin = $stmt_del_admin->execute();
            $stmt_del_admin->close();
            
            if ($result_del_admin) {
                $code = 200;
                $message = 'success';
                updatelogs("用户管理，删除用户角色，ID：" . $id); //记录操作日志
                $link->commit(); // 提交事务
            } else {
                $code = 100;
                $message = '删除关联用户失败！';
                $link->rollback(); // 回滚事务
            }
        } else {
            $code = 500;
            $message = '数据库预处理失败';
            $link->rollback(); // 回滚事务
        }
    } else {
        $code = 100;
        $message = '删除角色失败！';
        $link->rollback(); // 回滚事务
    }
} else {
    $code = 500;
    $message = '数据库预处理失败';
    $link->rollback(); // 回滚事务
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);