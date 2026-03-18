<?php
// 忘记密码 - 重置密码
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
$new_password = isset($_POST['new_password']) ? \basic\Basic::filterStr($_POST['new_password']) : ''; //新密码
$confirm_password = isset($_POST['confirm_password']) ? \basic\Basic::filterStr($_POST['confirm_password']) : ''; //确认密码
$new_password_iv = isset($_POST['new_password_iv']) ? \basic\Basic::filterStr($_POST['new_password_iv']) : ''; //iv 新密码解密用
$confirm_password_iv = isset($_POST['confirm_password_iv']) ? \basic\Basic::filterStr($_POST['confirm_password_iv']) : ''; //iv 确认密码解密用
$reset_token = isset($_POST['reset_token']) ? \basic\Basic::filterStr($_POST['reset_token']) : ''; //重置令牌

// 验证必要字段
if (empty($user_mobile) || empty($new_password) || empty($confirm_password) || empty($new_password_iv) || empty($confirm_password_iv) || empty($reset_token)) {
    echo $jsonData->jsonData(400, '缺少参数');
    exit;
}

try {
    // 开启session
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    
    // 检查session中是否有重置权限（通过验证手机验证码获得）
    $session_reset_mobile = isset($_SESSION['reset_mobile']) ? $_SESSION['reset_mobile'] : '';
    $session_reset_token = isset($_SESSION['reset_token']) ? $_SESSION['reset_token'] : '';
    $session_reset_time = isset($_SESSION['reset_token_time']) ? $_SESSION['reset_token_time'] : 0;

    // 验证session中的手机号
    if (empty($session_reset_mobile) || $session_reset_mobile !== $user_mobile) {
        throw new Exception('请先验证手机验证码！');
    }
    // 验证token
    if (!empty($reset_token) && $reset_token !== $session_reset_token) {
        throw new Exception('重置令牌无效！');
    }
    // 验证重置操作的有效期
    $current_time = time();
    $token_expire_time = 900; // 15分钟 = 900秒
    if (($current_time - $session_reset_time) > $token_expire_time) {
        // 清除session
        unset($_SESSION['reset_mobile']);
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_token_time']);
        throw new Exception('重置操作已过期，请重新验证！');
    }
    // 解密密码
    $password_decrypt_result = decryptIndex($new_password, $new_password_iv);
    if (!$password_decrypt_result['success']) throw new Exception('新密码解密失败: '.$password_decrypt_result['error']);
    $confirm_decrypt_result = decryptIndex($confirm_password, $confirm_password_iv);
    if (!$confirm_decrypt_result['success']) throw new Exception('确认密码解密失败: '.$confirm_decrypt_result['error']);
    
    $new_password_decrypted = $password_decrypt_result['data'];
    $confirm_password_decrypted = $confirm_decrypt_result['data'];
    
    // 验证两次密码是否一致
    if ($new_password_decrypted !== $confirm_password_decrypted) {
        throw new Exception('两次输入的密码不一致！');
    }
    
    // 验证密码强度（可选，根据需求添加）
    if (strlen($new_password_decrypted) < 8) {
        throw new Exception('密码长度不能少于8位！');
    }
    
    // 检查手机号是否已注册
    if (!haveSameUser($link, 'user_mobile', $user_mobile)) {
        throw new Exception('该手机号未注册！');
    }
    
    // 开启事务
    mysqli_autocommit($link, false);
    
    // 加密新密码
    $encrypted_password = my_crypt($new_password_decrypted, 1);
    
    // 更新密码
    $sql = "UPDATE user SET user_password = ? WHERE user_mobile = ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('SQL准备失败：' . $link->error);
    if (!$stmt->bind_param('ss', $encrypted_password, $user_mobile)) {
        throw new Exception('绑定参数失败：' . $stmt->error);
    }
    if (!$stmt->execute()) {
        throw new Exception('密码更新失败：' . $stmt->error);
    }
    
    // 检查是否成功更新
    if ($stmt->affected_rows === 0) {
        throw new Exception('未找到该用户或密码未更改！');
    }
    
    // 提交事务
    mysqli_commit($link);
    
    // 清除重置相关的session
    unset($_SESSION['reset_mobile']);
    unset($_SESSION['reset_token']);
    unset($_SESSION['reset_token_time']);
    
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
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);