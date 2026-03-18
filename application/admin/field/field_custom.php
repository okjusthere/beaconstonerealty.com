<?php
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

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = ['data' => [], 'total' => 0]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("field_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并过滤参数
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1;
$pagesize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize']) : 10;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';
$table_name = isset($_GET['tablename']) ? trim($_GET['tablename']) : '';

// 构建WHERE条件和参数
$where = '';
$params = [];
$types = '';
$whereClauses = [];

if (!empty($keywords)) {
    $whereClauses[] = "(field_name LIKE ? OR field_title LIKE ?)";
    $params[] = "%{$keywords}%";
    $params[] = "%{$keywords}%";
    $types .= 'ss';
}

if (!empty($table_name)) {
    $whereClauses[] = "table_name = ?";
    $params[] = $table_name;
    $types .= 's';
}

if (!empty($whereClauses)) {
    $where = " WHERE " . implode(" AND ", $whereClauses);
}

// 1. 先查询总数
$sqlTotal = "SELECT COUNT(*) AS total FROM field_custom" . $where;
$stmtTotal = $link->prepare($sqlTotal);

if (!$stmtTotal) {
    echo $jsonData->jsonData(500, '数据库查询准备失败: ' . $link->error, []);
    exit;
}

// 绑定参数
if (!empty($params)) {
    $stmtTotal->bind_param($types, ...$params);
}

// 执行总数查询
if ($stmtTotal->execute()) {
    $resultTotal = $stmtTotal->get_result();
    $totalRow = $resultTotal->fetch_assoc();
    $obj['total'] = (int)$totalRow['total'];
} else {
    echo $jsonData->jsonData(500, '数据库查询执行失败: ' . $stmtTotal->error, []);
    $stmtTotal->close();
    exit;
}
$stmtTotal->close();

// 2. 查询分页数据
$sqlData = "SELECT * FROM field_custom" . $where . " ORDER BY sort DESC, id DESC LIMIT ?, ?";
$stmtData = $link->prepare($sqlData);

if (!$stmtData) {
    echo $jsonData->jsonData(500, '数据库查询准备失败: ' . $link->error, []);
    exit;
}

// 计算分页
$start = ($currentpage - 1) * $pagesize;

// 绑定参数（注意顺序）
if (!empty($params)) {
    $stmtData->bind_param($types . 'ii', ...array_merge($params, [$start, $pagesize]));
} else {
    $stmtData->bind_param('ii', $start, $pagesize);
}

// 执行分页查询
if ($stmtData->execute()) {
    $result = $stmtData->get_result();
    $code = 200;
    $message = '查询成功！';
    
    while ($row = $result->fetch_assoc()) {
        $row["field_default_value"] = json_decode($row["field_default_value"]);
        $row["sort"] = (int)$row["sort"];
        $obj["data"][] = $row;
    }
} else {
    $code = 500;
    $message = '数据库查询执行失败: ' . $stmtData->error;
}

// 关闭语句和连接
$stmtData->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);