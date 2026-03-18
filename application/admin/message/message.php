<?php
// 获取客户留言表信息
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
if (!my_power("message_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit();
}

// 获取分页参数
$currentpage = isset($_GET['currentPage']) ? (int)$_GET['currentPage'] : 1;
$pagesize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 验证分页参数
if ($currentpage <= 0 || $pagesize <= 0) {
    echo $jsonData->jsonData(400, '无效的分页参数', $obj);
    exit();
}

// 构建基础SQL和条件
$base_sql = "SELECT * FROM message";
$count_sql = "SELECT COUNT(*) AS total FROM message";
$where = '';
$params = [];
$types = '';

// 添加搜索条件
if (!empty($keywords)) {
    $where = " WHERE title LIKE ? OR name LIKE ? OR phone LIKE ? OR email LIKE ?";
    $search_term = "%{$keywords}%";
    $params = array_fill(0, 4, $search_term);
    $types = str_repeat('s', count($params));
}

// 查询总数
$stmt_total = $link->prepare($count_sql . $where);
if ($stmt_total === false) {
    echo $jsonData->jsonData(500, '数据库查询失败: ' . $link->error, $obj);
    exit();
}

if (!empty($where)) {
    $stmt_total->bind_param($types, ...$params);
}

if (!$stmt_total->execute()) {
    echo $jsonData->jsonData(500, '数据库查询失败: ' . $stmt_total->error, $obj);
    $stmt_total->close();
    exit();
}

$result_total = $stmt_total->get_result();
$total = $result_total->fetch_assoc()['total'];
$stmt_total->close();

// 查询分页数据
$limit_sql = " ORDER BY id DESC LIMIT ?, ?";
$page_params = [($currentpage - 1) * $pagesize, $pagesize];
$page_types = 'ii';

$stmt = $link->prepare($base_sql . $where . $limit_sql);
if ($stmt === false) {
    echo $jsonData->jsonData(500, '数据库查询失败: ' . $link->error, $obj);
    exit();
}

// 绑定参数
if (!empty($where)) {
    $stmt->bind_param($types . $page_types, ...array_merge($params, $page_params));
} else {
    $stmt->bind_param($page_types, ...$page_params);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $code = 200;
    $message = '查询成功！';
    $obj['total'] = $total;
    
    while ($row = $result->fetch_assoc()) {
        $obj['data'][] = $row;
    }
} else {
    $message = '数据库查询失败: ' . $stmt->error;
}

$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);