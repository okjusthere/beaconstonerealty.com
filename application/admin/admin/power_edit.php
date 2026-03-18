<?php
// 编辑管理员角色信息

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
$requiredFields = ['id', 'sort', 'powername_before', 'powername', 'description', 'power'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field)) {
        echo $jsonData->jsonData(400, '缺少必要参数: ' . $field, []);
        exit;
    }
}

// 提取并过滤数据
$id = (int)$data->id;
$sort = (int)$data->sort;
$powername_before = trim($data->powername_before);
$powername = trim($data->powername);
$description = trim($data->description);
$power = json_encode($data->power); // 确保权限数据是有效的JSON

if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的权限数据格式', []);
    exit;
}

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("power_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 检查角色名称是否已存在（使用预处理）
if ($powername_before !== $powername) {
    $sql_have = "SELECT id FROM admin_power WHERE powername = ? AND id != ?";
    $stmt_have = $link->prepare($sql_have);
    if (!$stmt_have) {
        echo $jsonData->jsonData(500, '数据库查询准备失败', []);
        exit;
    }

    $stmt_have->bind_param("si", $powername, $id);
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
        echo $jsonData->jsonData($code, $message, $obj);
        exit;
    }
    $stmt_have->close();
}

// 执行更新（使用预处理）
updatePower($sort, $powername, $description, $power, $id, $code, $message);

// 更新记录的方法
function updatePower($sort, $powername, $description, $power, $id, &$code, &$message)
{
    global $link;
    
    $sql = "UPDATE admin_power SET sort = ?, powername = ?, description = ?, power = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        $code = 500;
        $message = '数据库更新准备失败: ' . $link->error;
        return;
    }
    
    $stmt->bind_param("isssi", $sort, $powername, $description, $power, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("账号角色管理，编辑角色，ID：" . $id);
        } else {
            $code = 404;
            $message = '未找到要更新的记录或数据未更改';
        }
    } else {
        $code = 100;
        $message = 'error';
    }
    
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);