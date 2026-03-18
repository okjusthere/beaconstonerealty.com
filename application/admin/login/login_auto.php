<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;
include_once '../../common.php'; //获取常量
// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 获取输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
$username = $_SESSION['manager_username'] ?? '';
$password = $_SESSION['manager_password'] ?? '';
$mobile = $_SESSION['manager_mobile'] ?? '';
$login_type = $data->type ?? '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

// 用户名密码自动登录
if ($login_type == "auto" && !empty($username) && !empty($password)) {
    // 远程验证
    $con = @file_get_contents(API_CHECK_ADMIN . '?username=' . my_crypt($username, 2) . '&password=' . my_crypt($password, 2));
    
    if (isset(json_decode($con, true)["message"]) && json_decode($con, true)["message"] == "login_success") {
        setcookie('AdminName', $username, 0, '/', $_SERVER['HTTP_HOST'], false, true);
        $_SESSION["manager"] = $username . 'super';
        $_SESSION["manager_username"] = $username;
        $_SESSION["manager_password"] = $password;

        $obj = [
            'qz_uname' => $username,
            'qz_power' => 'super',
            'qz_uname_crypt' => my_crypt($username, 2)
        ];
        echo $jsonData->jsonData(200, 'success', $obj);
        exit();
    }

    // 本地数据库验证
    $username_decrypted = my_crypt($username, 2);
    $sql = "SELECT * FROM admin WHERE username = ? AND password = ? AND state = '1'";
    $stmt = $link->prepare($sql);

    if ($stmt === false) {
        echo $jsonData->jsonData(500, '数据库查询失败', []);
        exit();
    }

    $stmt->bind_param("ss", $username_decrypted, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $username_encrypt = my_crypt($username_decrypted, 1);
        setcookie('AdminName', $username_encrypt, 0, '/', $_SERVER['HTTP_HOST'], false, true);
        $user_power = $res->fetch_assoc()['power'];
        $_SESSION["manager"] = $username_encrypt . $user_power;
        $_SESSION["manager_username"] = $username_encrypt;
        $_SESSION["manager_password"] = $password;

        $obj = [
            'qz_uname' => $username_encrypt,
            'qz_power' => $user_power,
            'qz_uname_crypt' => $username_decrypted
        ];
        echo $jsonData->jsonData(200, 'success', $obj);
    } else {
        echo $jsonData->jsonData(200, '用户名或密码不正确！', []);
    }

    $stmt->close();
    exit();
}

// 手机号自动登录
if ($login_type == "auto" && !empty($mobile)) {
    $username_decrypted = my_crypt($mobile, 2);
    $sql = "SELECT * FROM admin WHERE username = ? AND state = '1'";
    $stmt = $link->prepare($sql);

    if ($stmt === false) {
        echo $jsonData->jsonData(500, '数据库查询失败', []);
        exit();
    }

    $stmt->bind_param("s", $username_decrypted);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $username_encrypt = my_crypt($username_decrypted, 1);
        setcookie('AdminName', $username_encrypt, 0, '/', $_SERVER['HTTP_HOST'], false, true);
        $user_power = $res->fetch_assoc()['power'];
        $_SESSION["manager"] = $username_encrypt . $user_power;
        $_SESSION["manager_mobile"] = $username_encrypt;

        $obj = [
            'qz_uname' => $username_encrypt,
            'qz_power' => $user_power,
            'qz_uname_crypt' => $username_decrypted
        ];
        echo $jsonData->jsonData(200, 'success', $obj);
    } else {
        echo $jsonData->jsonData(200, '用户名或密码不正确！', []);
    }

    $stmt->close();
    exit();
}

// 无法自动登录的情况
echo $jsonData->jsonData(202, '自动登录失败', []);
mysqli_close($link);