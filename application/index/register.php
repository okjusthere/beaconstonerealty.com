<?php
//用户注册
//链接数据库
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

$regist_type = isset($_POST['regist_type']) ? \basic\Basic::filterInt($_POST['regist_type']) : 0; //请求方式{1：手机验证码注册；2：图形验证码注册}
$user_name = isset($_POST['user_name']) ? \basic\Basic::filterStr($_POST['user_name']) : ''; //用户名
$user_password = isset($_POST['user_password']) ? \basic\Basic::filterStr($_POST['user_password']) : ''; //用户密码
$user_password_confirm = isset($_POST['user_password_confirm']) ? \basic\Basic::filterStr($_POST['user_password_confirm']) : ''; //二次密码
$user_mobile = isset($_POST['user_mobile']) ? \basic\Basic::filterStr($_POST['user_mobile']) : ''; //注册手机号
$user_password_iv = isset($_POST['user_password_iv']) ? \basic\Basic::filterStr($_POST['user_password_iv']) : ''; //iv 解密用
$user_password_confirm_iv = isset($_POST['user_password_confirm_iv']) ? \basic\Basic::filterStr($_POST['user_password_confirm_iv']) : ''; //iv 二次密码解密用
// 验证码字段（根据注册类型区分验证码）
$_code = isset($_POST['code']) ? \basic\Basic::filterStr(strtolower($_POST['code'])) : ''; //图形验证码/手机验证码

$power = 1; //用户权限
$state = '1'; //用户状态，状态为1的时候是正常，为2是禁用
$add_time = time(); //注册时间

// 验证必要字段
if (empty($regist_type) || empty($user_name) || empty($user_password) || empty($user_password_confirm) || empty($user_password_iv) || empty($user_mobile)) {
    echo $jsonData->jsonData(400, '用户名、密码、手机号均为必填项');
    exit;
}

try {
    $password_decrypt_result = decryptIndex($user_password, $user_password_iv);
    if (!$password_decrypt_result['success']) throw new Exception('密码解密失败: '.$password_decrypt_result['error']);
    $password_confirm_decrypt_result = decryptIndex($user_password_confirm, $user_password_confirm_iv);
    if (!$password_confirm_decrypt_result['success']) throw new Exception('二次密码解密失败: '.$password_confirm_decrypt_result['error']);
    $user_password = $password_decrypt_result['data'];
    $user_password_confirm = $password_confirm_decrypt_result['data'];
    if ($user_password != $user_password_confirm) throw new Exception('两次密码不一致！');
    
    // 开启事务
    mysqli_autocommit($link, false);

    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    
    // 检查用户名和手机号是否已存在
    if (haveSameUser($link, 'user_name', $user_name)) throw new Exception('该用户名已注册！');
    if (haveSameUser($link, 'user_mobile', $user_mobile)) throw new Exception('该手机号已注册！');
    
    if ($regist_type == 1) {
        // 手机验证码注册
        if (empty($_code)) throw new Exception('手机验证码不能为空');
        
        $real_mobile_code = isset($_SESSION["mobile_code"]) ? $_SESSION["mobile_code"] : ''; //获取发送的验证码
        //判断验证码是否有误
        if (!($_code === $real_mobile_code)) throw new Exception('手机验证码错误！');

        $user_password = my_crypt($user_password, 1); //用户密码加密
        $sql = "INSERT INTO user (user_name, user_password, user_mobile, power, state, add_time) VALUES (?, ?, ?, ?, ?, ?)";
        
        //准备SQL语句
        $stmt = $link->prepare($sql);
        if (!$stmt) throw new Exception('手机验证码注册，SQL准备失败：' . $link->error);
        //绑定参数
        if (!$stmt->bind_param('sssiss', $user_name, $user_password, $user_mobile, $power, $state, $add_time)) throw new Exception('手机验证码注册，绑定参数失败：' . $stmt->error);
        //执行语句
        if (!$stmt->execute()) throw new Exception('手机验证码注册失败：' . $stmt->error);

        unset($_SESSION['mobile_code']);//注册成功，销毁之前的验证码session值
        
    } else if ($regist_type == 2) {
        // 图形验证码注册
        if (empty($_code)) throw new Exception('图形验证码不能为空');
        
        $real_code = isset($_SESSION["rel_captcha"]) ? strtolower($_SESSION["rel_captcha"]) : ''; //获取实际验证码

        //判断验证码是否有误
        if (!($_code === $real_code)) throw new Exception('图形验证码错误！');

        $user_password = my_crypt($user_password, 1); //用户密码加密
        $sql = "INSERT INTO user (user_name, user_password, user_mobile, power, state, add_time) VALUES (?, ?, ?, ?, ?, ?)";

        //准备SQL语句
        $stmt = $link->prepare($sql);
        if (!$stmt) throw new Exception('图形验证码注册，SQL语句准备失败：' . $link->error);
        //绑定参数
        if (!$stmt->bind_param('sssiss', $user_name, $user_password, $user_mobile, $power, $state, $add_time)) throw new Exception('图形验证码注册，绑定参数失败：' . $stmt->error);
        //执行语句
        if (!$stmt->execute()) throw new Exception('图形验证码注册失败：' . $stmt->error);

        unset($_SESSION['rel_captcha']); //销毁之前的验证码session值
    } else {
        throw new Exception('无效的注册类型！');
    }

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';

} catch (Exception $e) {
    // 回滚事务
    if (isset($link)) {
        mysqli_rollback($link);
        mysqli_autocommit($link, true);
    }

    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    // 清理旧变量
    if (isset($stmt_m) && $stmt_m instanceof mysqli_stmt) {
        $stmt_m->close();
    }
    if (isset($stmt_u) && $stmt_u instanceof mysqli_stmt) {
        $stmt_u->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);