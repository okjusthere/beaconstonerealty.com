<?php
// 获取新闻表信息
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

$code = 500;  // 默认响应码
$message = '未响应，请重试！';  // 默认响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 获取并验证参数
$table_name = isset($_GET['tn']) ? trim($_GET['tn']) : "";
$record_id = isset($_GET['rid']) ? (int)$_GET['rid'] : 0;

// 检查表名参数
if (empty($table_name)) {
    echo $jsonData->jsonData(400, '表名参数不能为空', $obj);
    exit();
}

// 验证表名格式
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
    echo $jsonData->jsonData(400, '表名包含非法字符', $obj);
    exit();
}

// 根据是否有record_id选择查询方式
if ($record_id > 0) {
    // 查询特定记录的字段信息
    $sql = "SELECT record_id, field_custom.field_name, field_title, field_content, field_type, field_default_value 
            FROM field_custom, field_info 
            WHERE record_id = ? 
            AND field_custom.table_name = ? 
            AND field_info.table_name = ? 
            AND field_custom.field_name = field_info.field_name 
            ORDER BY field_custom.sort DESC, field_custom.id DESC";
    
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
        exit();
    }
    
    $stmt->bind_param("iss", $record_id, $table_name, $table_name);
    if (!$stmt->execute()) {
        $stmt->close();
        echo $jsonData->jsonData(500, '数据库查询失败', $obj);
        exit();
    }
    
    $res = $stmt->get_result();
    $stmt->close();
} else {
    // 查询表的所有字段信息
    $sql = "SELECT * FROM field_custom WHERE table_name = ? ORDER BY sort DESC,id DESC";
    
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', $obj);
        exit();
    }
    
    $stmt->bind_param("s", $table_name);
    if (!$stmt->execute()) {
        $stmt->close();
        echo $jsonData->jsonData(500, '数据库查询失败', $obj);
        exit();
    }
    
    $res = $stmt->get_result();
    $stmt->close();
}

// 处理查询结果
if (!$res) {
    echo $jsonData->jsonData(500, '获取结果集失败', $obj);
    exit();
}

$code = 200;
$message = '查询成功！';
$obj["data"] = array();

while ($row = $res->fetch_assoc()) {
    // 处理字段内容
    if ($record_id == 0) {
        $row["field_content"] = in_array($row["field_type"], [3, 5, 6, 7]) ? array() : "";
    }

    // 处理默认值
    if (in_array($row["field_type"], [2, 3])) {
        $row["field_default_value"] = json_decode($row["field_default_value"]);
    }

    // 处理特定记录的内容
    if ($record_id > 0 && in_array($row["field_type"], [3, 5, 6, 7])) {
        $row["field_content"] = json_decode($row["field_content"]);
    }

    $obj["data"][] = $row;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);