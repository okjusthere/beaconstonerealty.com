<?php
// 获取嵌入代码信息
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$basic = new \basic\Basic();

// 初始化响应数据
$response = [
    'code' => 500,
    'message' => '未响应，请重试！',
    'data' => []
];

header('Content-Type: application/json; charset=utf-8');

// 验证数据库连接
if (!$link) {
    $response['message'] = '数据库连接失败';
    echo $jsonData->jsonData($response['code'], $response['message'], $response['data']);
    exit;
}

// 过滤输入
$url_referrer = isset($_POST['url_referrer']) ? $basic::filterStr($_POST['url_referrer']) : '';
$url = isset($_POST['url']) ? $basic::filterStr($_POST['url']) : '';

// 1. 检查流量统计状态
$stateQuery = "SELECT state FROM tb_traffic_statistics_state WHERE id = 1";
$stateResult = mysqli_query($link, $stateQuery);

if (!$stateResult) {
    $response['message'] = '查询状态失败: ' . mysqli_error($link);
    echo $jsonData->jsonData($response['code'], $response['message'], $response['data']);
    mysqli_close($link);
    exit;
}

$stateRow = mysqli_fetch_assoc($stateResult);
$state = (int)$stateRow['state'];

// 流量统计未开启
if ($state !== 1) {
    $response['code'] = 200;
    $response['message'] = '流量统计未开启';
    echo $jsonData->jsonData($response['code'], $response['message'], $response['data']);
    mysqli_close($link);
    exit;
}

// 2. 记录访问数据
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$visitTime = time();

// 使用预处理语句防止SQL注入
$checkQuery = "SELECT id, page_view FROM tb_traffic_statistics 
              WHERE ip = ? AND user_agent = ? AND DATE(FROM_UNIXTIME(visit_time)) = CURDATE()";
$stmt = mysqli_prepare($link, $checkQuery);
mysqli_stmt_bind_param($stmt, 'ss', $ip, $userAgent);
mysqli_stmt_execute($stmt);
$checkResult = mysqli_stmt_get_result($stmt);

if ($checkResult && mysqli_num_rows($checkResult) > 0) {
    // 更新现有记录
    $row = mysqli_fetch_assoc($checkResult);
    $recordId = (int)$row['id'];
    $pageView = json_decode($row['page_view'], true) ?: [];
    $pageView[] = $url;

    $updateQuery = "UPDATE tb_traffic_statistics SET page_view = ? WHERE id = ?";
    $stmt = mysqli_prepare($link, $updateQuery);
    $pageViewJson = json_encode($pageView);
    mysqli_stmt_bind_param($stmt, 'si', $pageViewJson, $recordId);

    if (mysqli_stmt_execute($stmt)) {
        $response['code'] = 200;
        $response['message'] = 'success';
    }
} else {
    // 插入新记录
    $pageView = [$url];
    $insertQuery = "INSERT INTO tb_traffic_statistics 
                   (ip, user_agent, page_view, page_referrer, visit_time) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $insertQuery);
    $pageViewJson = json_encode($pageView);
    mysqli_stmt_bind_param($stmt, 'sssss', $ip, $userAgent, $pageViewJson, $url_referrer, $visitTime);

    if (mysqli_stmt_execute($stmt)) {
        $response['code'] = 200;
        $response['message'] = 'success';
    }
}

// 关闭连接并输出结果
mysqli_close($link);
echo $jsonData->jsonData($response['code'], $response['message'], $response['data']);