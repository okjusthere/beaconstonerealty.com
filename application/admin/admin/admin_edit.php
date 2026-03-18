<?php
//编辑管理员信息

//查看是否有访问权限
include_once '../checking_user.php';

//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
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
$requiredFields = ['id', 'power', 'username_before', 'username', 'state'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field)) {
        echo $jsonData->jsonData(400, '缺少必要参数: ' . $field, []);
        exit;
    }
}

// 提取并过滤数据
$id = (int)$data->id;
$power = (int)$data->power;
$username_before = trim($data->username_before);
$username = trim($data->username);
$password = isset($data->password) ? trim($data->password) : '';
$remarks = isset($data->remarks) ? trim($data->remarks) : '';
$state = trim($data->state);

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("admin_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 密码加密
if (!empty($password)) {
    $password = my_crypt($password, 1);
} else {
    // 如果不修改密码，保留原密码
    $password = null;
}

// 检查用户名是否已存在（使用预处理）
if ($username_before != $username) {
    $sql_have = "SELECT id FROM admin WHERE username = ?";
    $stmt_have = $link->prepare($sql_have);
    if (!$stmt_have) {
        echo $jsonData->jsonData(500, '数据库查询准备失败', []);
        exit;
    }

    $stmt_have->bind_param("s", $username);
    if (!$stmt_have->execute()) {
        echo $jsonData->jsonData(500, '数据库查询执行失败', []);
        $stmt_have->close();
        exit;
    }

    $res_have = $stmt_have->get_result();
    if ($res_have->num_rows > 0) {
        $code = 101;
        $message = '该用户名已存在！';
        $stmt_have->close();
        echo $jsonData->jsonData($code, $message, $obj);
        exit;
    }
    $stmt_have->close();
}

// 执行更新（使用预处理）
updateAdmin($username, $password, $remarks, $power, $state, $id, $code, $message);

// 更新记录的方法
function updateAdmin($username, $password, $remarks, $power, $state, $id, &$code, &$message)
{
    global $link;
    
    // 构建SQL语句和参数
    $params = [];
    $setClauses = [];
    
    $setClauses[] = "username = ?";
    $params[] = $username;
    
    if ($password !== null) {
        $setClauses[] = "password = ?";
        $params[] = $password;
    }
    
    $setClauses[] = "remarks = ?";
    $params[] = $remarks;
    
    $setClauses[] = "power = ?";
    $params[] = $power;
    
    $setClauses[] = "state = ?";
    $params[] = $state;
    
    $setClause = implode(', ', $setClauses);
    $sql = "UPDATE admin SET $setClause WHERE id = ?";
    $params[] = $id;
    
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        $message = '数据库更新准备失败: ' . $link->error;
        return;
    }
    
    // 动态绑定参数
    $types = str_repeat('s', count($params) - 1) . 'i'; // 所有参数都是字符串，最后一个是ID（整数）
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("账号管理，编辑账号，ID：" . $id);
        } else {
            $code = 404;
            $message = '未找到要更新的记录或数据未更改';
        }
    } else {
        $code = 100;
        $message = '更新失败: ' . $stmt->error;
    }
    
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);