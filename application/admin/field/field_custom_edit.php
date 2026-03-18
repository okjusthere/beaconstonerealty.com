<?php
// 添加文章信息
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input'); // 获取非表单数据
if ($data === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit();
}

$data = json_decode($data); // 解码axios传递过来的json数据
if ($data === null) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit();
}

// 验证必要字段
if (!isset($data->sort, $data->table_name_before, $data->field_name_before, $data->id, 
    $data->table_name, $data->field_name, $data->field_title, $data->field_type)) {
    echo $jsonData->jsonData(400, '缺少必要参数', []);
    exit();
}

$sort = $data->sort; // 自定义字段排序
$table_name_before = $data->table_name_before; // 没改之前的表名
$field_name_before = $data->field_name_before; // 没改之前的字段名
$id = (int)$data->id; // 记录ID
$table_name = $data->table_name; // 表名
$field_name = $data->field_name; // 字段名
$field_title = $data->field_title; // 字段描述
$field_type = $data->field_type; // 字段类型
$field_default_value = isset($data->field_default_value) && count($data->field_default_value) > 0 
    ? json_encode($data->field_default_value, JSON_UNESCAPED_UNICODE) 
    : ''; // 字段默认值

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("field_edit")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit();
}

// 验证表名和字段名是否合法
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name) || !preg_match('/^[a-zA-Z0-9_]+$/', $field_name)) {
    echo $jsonData->jsonData(400, '表名或字段名包含非法字符', $obj);
    exit();
}

if ($field_name_before === $field_name && $table_name_before === $table_name) {
    // 使用预处理语句修改字段信息
    $sql = "UPDATE field_custom SET table_name=?, field_name=?, field_title=?, field_type=?, field_default_value=?, sort=? WHERE id=?";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
        exit();
    }
    
    $stmt->bind_param("sssssii", $table_name, $field_name, $field_title, $field_type, $field_default_value, $sort, $id);
    $res = $stmt->execute();
    $stmt->close();
    
    if (!$res) {
        echo $jsonData->jsonData(500, '数据库操作失败', $obj);
        exit();
    }
    
    $code = 200;
    $message = 'success';
    
    // 检查并更新字段信息
    if (!checkFiledInfo($table_name, $field_name, $field_type)) {
        $message = '更新成功，但同步字段信息失败';
    }
}

/**
 * 检查并更新字段信息
 */
function checkFiledInfo($table_name, $field_name, $field_type): bool
{
    global $link;
    
    // 查询需要添加字段的记录
    $sql = "SELECT id FROM {$table_name} WHERE id NOT IN(SELECT record_id FROM field_info WHERE table_name=? AND field_name=?)";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ss", $table_name, $field_name);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    
    $res = $stmt->get_result();
    $stmt->close();
    
    if (!$res) {
        return false;
    }
    
    $number = $res->num_rows;
    if ($number <= 0) {
        return true;
    }
    
    $id_ary = [];
    while ($row = $res->fetch_assoc()) {
        $id_ary[] = $row["id"];
    }
    
    $field_content = in_array($field_type, [3, 5, 6, 7]) ? json_encode([]) : "";
    $add_time = time();
    
    // 准备插入语句
    $sql_field_info = "INSERT INTO field_info(table_name, record_id, field_name, field_content, add_time) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $link->prepare($sql_field_info);
    
    if (!$stmt_insert) {
        return false;
    }
    
    $stmt_insert->bind_param("sisss", $table_name, $record_id, $field_name, $field_content, $add_time);
    
    // 开始事务
    $link->begin_transaction();
    $success = true;
    
    foreach ($id_ary as $value) {
        $record_id = $value;
        if (!$stmt_insert->execute()) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $link->commit();
    } else {
        $link->rollback();
    }
    
    $stmt_insert->close();
    return $success;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);