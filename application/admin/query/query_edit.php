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
if (!my_power("query_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 验证必要字段
if (!isset($data->id) || !is_numeric($data->id)) {
    $code = 400;
    $message = '无效的记录ID';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

if (!isset($data->q_condition) || !isset($data->content) || !isset($data->state)) {
    $code = 400;
    $message = '缺少必要参数！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 准备数据
$id = (int)$data->id;
$q_condition = $data->q_condition;
$thumbnail = isset($data->thumbnail) && is_array($data->thumbnail) ?
    (count($data->thumbnail) == 0 ? '' : json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE)) : '';
$content = $data->content;
$state = $data->state;
$field = isset($data->field) ? $data->field : array();

try {
    // 开启事务
    mysqli_autocommit($link, false);

    // 更新查询表记录（使用预处理语句）
    $sql_query = "UPDATE query SET q_condition=?, thumbnail=?, content=?, state=? WHERE id=?";
    $stmt_query = $link->prepare($sql_query);

    if (!$stmt_query) throw new Exception('更新查询信息，数据库操作准备失败: ' . $link->error);

    $stmt_query->bind_param("ssssi", $q_condition, $thumbnail, $content, $state, $id);
    $result = $stmt_query->execute();

    // 更新自定义字段
    if (!empty($field)) {
        $sql_field = "UPDATE field_info SET field_content = ? WHERE record_id = ? AND table_name = 'query' AND field_name = ?";
        // 准备更新语句
        $stmt_field = $link->prepare($sql_field);
        if (!$stmt_field) throw new Exception('自定义字段更新准备失败');
        foreach ($field as $value) {
            $value = object_array($value);
            $field_content = $value["field_content"];

            if (in_array($value["field_type"], [3, 5, 6, 7])) {
                $field_content = json_encode($field_content, JSON_UNESCAPED_UNICODE);
            }

            //绑定参数
            $stmt_field->bind_param("sis", $field_content, $id, $value["field_name"]);
            //执行更新
            $result_field = $stmt_field->execute();
            if (!$result_field) throw new Exception('部分自定义字段更新失败');
        }
    }

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';

} catch (Exception $e) {
    // 回滚事务
    if (isset($link)) {
        mysqli_rollback($link);
        mysqli_autocommit($link, true);
    }

    $code = 100;
    $message = $e->getMessage();

} finally {
    // 清理资源
    if (isset($stmt_query) && $stmt_query instanceof mysqli_stmt) {
        $stmt_query->close();
    }
    if (isset($stmt_field) && $stmt_field instanceof mysqli_stmt) {
        $stmt_field->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);