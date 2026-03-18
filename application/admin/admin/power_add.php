<?php
// 新增管理员角色信息

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

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 验证必要字段
$requiredFields = ['sort', 'powername', 'description'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field)) {
        echo $jsonData->jsonData(400, '缺少必要参数: ' . $field, []);
        exit;
    }
}

// 提取并过滤数据
$sort = (int)$data->sort;
$powername = trim($data->powername);
$description = trim($data->description);
$power = json_encode([]); // 初始化为空权限数组

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("power_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 检查角色名称是否已存在（使用预处理）
$sql_have = "SELECT id FROM admin_power WHERE powername = ?";
$stmt_have = $link->prepare($sql_have);
if (!$stmt_have) {
    echo $jsonData->jsonData(500, '数据库查询准备失败', []);
    exit;
}

$stmt_have->bind_param("s", $powername);
if (!$stmt_have->execute()) {
    echo $jsonData->jsonData(500, '数据库查询执行失败', []);
    $stmt_have->close();
    exit;
}

$res_have = $stmt_have->get_result();
if ($res_have->num_rows > 0) {
    $code = 101;
    $message = '该角色名称已存在！';
    $stmt_have->close();
} else {
    $stmt_have->close();
    
    // 插入新角色（使用预处理）
    $sql = "INSERT INTO admin_power (sort, powername, description, power) VALUES (?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库插入准备失败', []);
        exit;
    }

    $stmt->bind_param("isss", $sort, $powername, $description, $power);
    if ($stmt->execute()) {
        $code = 200;
        $message = 'success';
        $r_id = $stmt->insert_id;
        updatelogs("账号管理，添加账号角色，ID：" . $r_id);
    } else {
        $code = 100;
        $message = 'error';
    }
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);