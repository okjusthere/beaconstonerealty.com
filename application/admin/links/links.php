<?php
// 获取友情链接表信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;

include_once '../../../myclass/ResponseJson.php';

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 默认响应码
$message = '未响应，请重试！';  // 默认响应信息
$obj = ['data' => []];

header('Content-Type:application/json; charset=utf-8');

// 权限检查（已注释，按需启用）
if (!my_power("links_list")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', $obj);
    exit;
}

// 获取并验证参数
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1;
$pagesize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize']) : 10;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';
$classid = isset($_GET['classid']) ? trim($_GET['classid']) : '';

// 准备基础SQL和参数
$sql = "SELECT * FROM tb_links";
$where = [];
$params = [];
$param_types = "";

// 处理关键词搜索条件
if (!empty($keywords)) {
    $where[] = "(title LIKE ? OR url LIKE ?)";
    $search_param = "%{$keywords}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

// 处理分类ID条件
if (!empty($classid)) {
    $where[] = "classid LIKE ?";
    $params[] = '%"'.$classid.'"%';
    $param_types .= "s";
}

// 构建完整SQL
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY is_show ASC, is_top DESC, sort DESC, id DESC";

// 获取总记录数
$stmt_total = $link->prepare($sql);
if (!$stmt_total) {
    echo $jsonData->jsonData(500, '查询准备失败', $obj);
    exit;
}

// 绑定参数
if (!empty($params)) {
    $stmt_total->bind_param($param_types, ...$params);
}

if (!$stmt_total->execute()) {
    $stmt_total->close();
    echo $jsonData->jsonData(500, '查询执行失败', $obj);
    exit;
}

$result_total = $stmt_total->get_result();
$total = $result_total->num_rows;
$stmt_total->close();

// 获取分页数据
$start = ($currentpage - 1) * $pagesize;
$sql_page = $sql . " LIMIT ?, ?";
$params[] = $start;
$params[] = $pagesize;
$param_types .= "ii";

$stmt_page = $link->prepare($sql_page);
if (!$stmt_page) {
    echo $jsonData->jsonData(500, '分页查询准备失败', $obj);
    exit;
}

// 绑定参数
$stmt_page->bind_param($param_types, ...$params);

if (!$stmt_page->execute()) {
    $stmt_page->close();
    echo $jsonData->jsonData(500, '分页查询执行失败', $obj);
    exit;
}

$result_page = $stmt_page->get_result();

// 处理结果数据
$code = 200;
$message = '查询成功！';
$obj["total"] = $total;

while ($row = $result_page->fetch_assoc()) {
    // 转换数据类型
    $row["sort"] = (int)$row["sort"];
    $row["classid"] = json_decode($row["classid"]);
    $row["is_top"] = ($row["is_top"] != '1'); // 反转逻辑，1=false,其他=true
    $row["thumbnail"] = json_decode($row["thumbnail"]);
    $row["edit_show"] = true;
    
    $obj["data"][] = $row;
}

$stmt_page->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);