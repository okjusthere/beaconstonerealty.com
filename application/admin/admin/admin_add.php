<?php
//新增管理员信息

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

// 获取并解析JSON输入
$data = json_decode(file_get_contents('php://input'));
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 验证必要字段
if (!isset($data->username, $data->password, $data->power)) {
    echo $jsonData->jsonData(400, '缺少必要参数', []);
    exit;
}

// 提取数据
$username = trim($data->username); //用户名
$password = $data->password; //密码
$remarks = isset($data->remarks) ? trim($data->remarks) : ''; //备注
$power = (int)$data->power; //权限等级{跟权限表ID对应}
$state = isset($data->state) ? trim($data->state) : '1'; //用户状态
$add_time = time(); //添加时间

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("admin_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 密码加密
if (!empty($password)) {
    $password = my_crypt($password, 1);
}

// 检查用户名是否存在（使用预处理）
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
} else {
    $stmt_have->close();
    
    // 插入新管理员（使用预处理）
    $sql = "INSERT INTO admin (username, password, remarks, power, state, add_time) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库插入准备失败', []);
        exit;
    }

    $stmt->bind_param("sssiss", $username, $password, $remarks, $power, $state, $add_time);
    if ($stmt->execute()) {
        $code = 200;
        $message = 'success';
        $r_id = $stmt->insert_id;
        updatelogs("账号管理，添加账号，ID：" . $r_id);
    } else {
        $code = 100;
        $message = '数据库插入失败: ' . $stmt->error;
    }
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);