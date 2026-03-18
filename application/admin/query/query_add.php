<?php
//新增查询信息
//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//引用自定义函数
include_once "../function.php";

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 初始化响应数据
$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("query_add")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要字段
if (!isset($data->q_condition) || !isset($data->content) || !isset($data->state)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备数据
$q_condition = $data->q_condition;
$thumbnail = isset($data->thumbnail) && is_array($data->thumbnail) && count($data->thumbnail) > 0 ? 
             json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';
$content = $data->content;
$state = $data->state;
$field = isset($data->field) ? $data->field : array();
$add_time = time();

// 向查询表插入数据（使用预处理语句）
$sql_query = "INSERT INTO query (q_condition, thumbnail, content, state, add_time) VALUES (?, ?, ?, ?, ?)";
$stmt_query = $link->prepare($sql_query);

if ($stmt_query) {
    $stmt_query->bind_param("sssss", $q_condition, $thumbnail, $content, $state, $add_time);
    $result = $stmt_query->execute();
    
    if ($result) {
        $q_id = $stmt_query->insert_id;
        $code = 200;
        $message = 'success';
        
        // 向字段信息表插入数据（如果有字段数据）
        if (is_array($field) && count($field) > 0) {
            $sql_field = "INSERT INTO field_info (table_name, record_id, field_name, field_content, add_time) VALUES (?, ?, ?, ?, ?)";
            $stmt_field = $link->prepare($sql_field);
            
            if ($stmt_field) {
                $success = true;
                
                foreach ($field as $value) {
                    $value = object_array($value);
                    $field_content = $value["field_content"];
                    
                    // 处理需要JSON编码的字段类型
                    if (in_array($value["field_type"], [3, 5, 6, 7])) {
                        $field_content = json_encode($field_content, JSON_UNESCAPED_UNICODE);
                    }
                    
                    $stmt_field->bind_param(
                        "sisss", 
                        $value["table_name"], 
                        $q_id, 
                        $value["field_name"], 
                        $field_content, 
                        $add_time
                    );
                    
                    if (!$stmt_field->execute()) {
                        $success = false;
                        break;
                    }
                }
                
                if (!$success) {
                    $code = 100;
                    $message = '出错了，请联系管理员！';
                }
                
                $stmt_field->close();
            } else {
                $code = 100;
                $message = '出错了，请联系管理员！';
            }
        }
    } else {
        $code = 100;
        $message = '出错了，请联系管理员！';
    }
    
    $stmt_query->close();
} else {
    $code = 500;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);