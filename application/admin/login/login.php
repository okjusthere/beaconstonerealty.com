<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;
include_once '../../common.php'; //获取常量
include '../function.php'; // 引用自定义function
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

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 获取用户输入
$username = $data->username ?? '';
$password = $data->password ?? '';
$iv = $data->iv ?? '';
$password = decrypt($password, $iv);
$code_user = isset($data->code) ? strtolower($data->code) : '';
$area_code = $data->area_code ?? '';
$mobile = $data->mobile ?? '';
$mobile_code = $data->mobile_code ?? 0;
$autologin = $data->autologin ?? false;

$code = 500;
$message = '未响应，请重试！';
$obj = [];

// 手机验证码登录流程
if (!empty($area_code) && !empty($mobile) && !empty($mobile_code)) {
    // 验证手机验证码
    $rel_captcha_mobile = $_SESSION['admin_mobile_code'] ?? '';
    if ($mobile_code != $rel_captcha_mobile || empty($rel_captcha_mobile)) {
        echo $jsonData->jsonData(100, '验证码错误，请重新输入！', []);
        exit();
    }

    // 使用预处理语句查询用户
    $sql = "SELECT * FROM admin WHERE username = ? AND state = '1'";
    $stmt = $link->prepare($sql);

    if ($stmt === false) {
        echo $jsonData->jsonData(500, '数据库查询失败', []);
        exit();
    }

    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $_SESSION['admin_mobile_code'] = '';
        $username_encrypt = my_crypt($mobile, 1);

        // 设置cookie和session
        setcookie('AdminName', $username_encrypt, 0, '/', $_SERVER['HTTP_HOST'], false, true);
        $user_power = $res->fetch_assoc()['power'];
        $_SESSION["manager"] = $username_encrypt . $user_power;
        $_SESSION["manager_mobile"] = $username_encrypt;

        updatelogs("登录成功");

        // 自动登录处理
        if ($autologin) {
            $session_id = session_id();
            setcookie("PHPSESSID", $session_id, time() + 3600 * 24 * 15, "/", $_SERVER["HTTP_HOST"], false, true);
        }

        $obj = ['qz_uname' => $username_encrypt, 'qz_power' => $user_power];
        echo $jsonData->jsonData(200, 'success', $obj);
    } else {
        updatelogs("登录失败，手机号输入有误，输入手机号：" . $mobile);
        echo $jsonData->jsonData(100, '手机号输入有误！', []);
    }

    $_SESSION['rel_captcha'] = mt_rand(10000, 99999); //销毁之前的验证码session值

    $stmt->close();
    exit();
}

// 用户名密码登录流程
$rel_captcha = strtolower($_SESSION['rel_captcha'] ?? '');
if ($code_user != $rel_captcha) {
    //setcookie("rel_captcha", "", time() - 3600);
    $_SESSION['rel_captcha'] = mt_rand(10000, 99999); //销毁之前的验证码session值
    echo $jsonData->jsonData(100, '验证码错误，请重新输入！', []);
    exit();
}

// 远程验证
$con = @file_get_contents(API_CHECK_ADMIN . '?username=' . $username . '&password=' . $password);
if (isset(json_decode($con, true)["message"]) && json_decode($con, true)["message"] == "login_success") {
    $username_encrypt = my_crypt($username, 1);
    setcookie('AdminName', $username_encrypt, 0, '/', $_SERVER['HTTP_HOST'], false, true);
    $_SESSION["manager"] = $username_encrypt . 'super';
    $_SESSION["manager_username"] = $username_encrypt;
    $_SESSION["manager_password"] = my_crypt($password, 1);

    updatelogs("登录成功");

    if ($autologin) {
        $session_id = session_id();
        setcookie("PHPSESSID", $session_id, time() + 3600 * 24 * 15, "/", $_SERVER["HTTP_HOST"], false, true);
    }

    echo $jsonData->jsonData(200, 'success', ['qz_uname' => $username_encrypt, 'qz_power' => 'super']);
    exit();
}

// 本地数据库验证
$password_encrypted = my_crypt($password, 1);
$sql = "SELECT * FROM admin WHERE username = ? AND password = ? AND state = '1'";
$stmt = $link->prepare($sql);

if ($stmt === false) {
    echo $jsonData->jsonData(500, '数据库查询失败', []);
    exit();
}

$stmt->bind_param("ss", $username, $password_encrypted);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 1) {
    $username_encrypt = my_crypt($username, 1);
    setcookie('AdminName', $username_encrypt, 0, '/', $_SERVER['HTTP_HOST'], false, true);
    $user_power = $res->fetch_assoc()['power'];
    $_SESSION["manager"] = $username_encrypt . $user_power;
    $_SESSION["manager_username"] = $username_encrypt;
    $_SESSION["manager_password"] = $password_encrypted;

    updatelogs("登录成功");

    if ($autologin) {
        $session_id = session_id();
        setcookie("PHPSESSID", $session_id, time() + 3600 * 24 * 15, "/", $_SERVER["HTTP_HOST"], false, true);
    }

    echo $jsonData->jsonData(200, 'success', ['qz_uname' => $username_encrypt, 'qz_power' => $user_power]);
} else {
    updatelogs("登录失败，用户名或密码不正确，输入用户名：" . $username);
    echo $jsonData->jsonData(100, '用户名或密码不正确！', []);
}

$stmt->close();
mysqli_close($link);