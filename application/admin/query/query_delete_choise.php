<?php
//复选框 删除文章
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
if (!my_power("query_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 验证输入数据
if (!is_array($data) || count($data) === 0) {
    $code = 400;  //响应码
    $message = '请选择要删除的项目！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 过滤和验证ID数组
$ids = array_filter($data, 'is_numeric');
if (count($ids) === 0) {
    $code = 400;  //响应码
    $message = '无效的ID参数！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 使用事务确保数据一致性
$link->begin_transaction();

// 准备IN语句的参数占位符
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// 删除主表记录
$sql = "DELETE FROM query WHERE id IN ($placeholders)";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 动态绑定参数
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $code = 200;
        $message = 'success';
        
        // 删除关联的字段信息记录
        $sql_field_info = "DELETE FROM field_info WHERE record_id IN ($placeholders)";
        $stmt_field = $link->prepare($sql_field_info);
        
        if ($stmt_field) {
            $stmt_field->bind_param($types, ...$ids);
            $result_field = $stmt_field->execute();
            $stmt_field->close();
            
            if ($result_field) {
                $link->commit(); // 提交事务
            } else {
                $code = 100;
                $message = '删除关联数据失败，请重试或联系管理员！';
                $link->rollback(); // 回滚事务
            }
        } else {
            $code = 500;
            $message = '数据库预处理失败';
            $link->rollback(); // 回滚事务
        }
    } else {
        $code = 100;
        $message = '删除失败，请重试或联系管理员！';
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