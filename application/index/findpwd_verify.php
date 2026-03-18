<?php
// 忘记密码 - 验证手机验证码
// 链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
include_once "function.php"; //引用自定义函数
include_once '../../myclass/ResponseJson.php'; //获取自定义的返回json的函数
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$user_mobile = isset($_POST['user_mobile']) ? \basic\Basic::filterStr($_POST['user_mobile']) : ''; //手机号
$mobile_code = isset($_POST['sms_code']) ? \basic\Basic::filterStr($_POST['sms_code']) : ''; //手机验证码

if (empty($user_mobile) || empty($mobile_code)) {
    echo $jsonData->jsonData(400, '手机号和验证码均为必填项');
    exit;
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
    // 检查手机号是否已注册
    if (!haveSameUser($link, 'user_mobile', $user_mobile)) {
        throw new Exception('该手机号未注册！');
    }
    
    // 获取session中存储的验证码
    $real_mobile_code = isset($_SESSION["mobile_code"]) ? $_SESSION["mobile_code"] : '';
    
    // 验证验证码
    if (empty($real_mobile_code)) {
        throw new Exception('验证码已过期，请重新获取！');
    }
    
    if ($mobile_code !== $real_mobile_code) {
        throw new Exception('验证码错误！');
    }
    
    // 验证通过，生成重置密码token
    $reset_token = md5($user_mobile . time() . rand(1000, 9999));
    
    // 将token存入session，用于后续重置密码验证
    $_SESSION['reset_token'] = $reset_token;
    $_SESSION['reset_mobile'] = $user_mobile;
    $_SESSION['reset_token_time'] = time(); // 记录token生成时间，可用于设置有效期
    
    // 验证成功后清除验证码session，防止重复使用
    unset($_SESSION['mobile_code']);
    
    $obj['reset_token'] = $reset_token;
    $obj['reset_mobile'] = $user_mobile;
    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);