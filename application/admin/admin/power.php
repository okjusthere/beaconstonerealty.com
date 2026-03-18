<?php
// 获取管理员权限表信息

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
$obj = ['data' => []]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("power_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并过滤搜索关键词
$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 准备SQL查询
$sql = 'SELECT * FROM admin_power';
$params = [];
$types = '';

// 添加搜索条件
if (!empty($keywords)) {
    $sql .= ' WHERE powername LIKE ?';
    $params[] = "%{$keywords}%";
    $types .= 's'; // s表示字符串类型
}

// 添加排序
$sql .= ' ORDER BY sort DESC, id ASC';

// 准备预处理语句
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库查询准备失败: ' . $link->error, []);
    exit;
}

// 绑定参数
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// 执行查询
if (!$stmt->execute()) {
    echo $jsonData->jsonData(500, '数据库查询执行失败: ' . $stmt->error, []);
    $stmt->close();
    exit;
}

// 获取结果
$result = $stmt->get_result();
if ($result) {
    $code = 200;
    $message = 'success';
    
    while ($row = $result->fetch_assoc()) {
        // 解码JSON格式的权限数据
        $row['power'] = json_decode($row['power'], true);
        $obj['data'][] = $row;
    }
} else {
    $message = '获取查询结果失败';
}

// 关闭语句和连接
$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);