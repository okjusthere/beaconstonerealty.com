<?php
//复选框删除管理员

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

// 验证输入数据
if (!is_array($data) || empty($data)) {
    $code = 400;  //响应码
    $message = '请选择要删除的角色！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 过滤和验证ID数组
$ids = array_filter($data, 'is_numeric');
if (empty($ids)) {
    $code = 400;  //响应码
    $message = '无效的角色ID参数！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备IN语句的参数占位符
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

// 使用事务确保数据一致性
$link->begin_transaction();

// 使用预处理语句批量删除角色
$sql = "DELETE FROM user_power WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param($types, ...$ids);
    $result = $stmt->execute();
    
    if ($result) {
        // 使用预处理语句批量删除关联用户
        $sql_del_admin = "DELETE FROM user WHERE power IN ($placeholders)";
        $stmt_del_admin = $link->prepare($sql_del_admin);
        
        if ($stmt_del_admin) {
            $stmt_del_admin->bind_param($types, ...$ids);
            $result_del_admin = $stmt_del_admin->execute();
            
            if ($result_del_admin) {
                $code = 200;
                $message = 'success';
                updatelogs("用户管理，批量删除用户角色，ID：" . implode(',', $ids));
                $link->commit(); // 提交事务
            } else {
                $code = 100;
                $message = '删除关联用户失败！';
                $link->rollback(); // 回滚事务
            }
            $stmt_del_admin->close();
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
    $stmt->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
    $link->rollback(); // 回滚事务
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);