<?php
//获取网站开关信息
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

$regist_type = isset($_POST['login_type']) ? \basic\Basic::filterInt($_POST['login_type']) : 0; //请求方式{1：手机号登录；2，账号密码登录}
$user_name = isset($_POST['user_name']) ? \basic\Basic::filterStr($_POST['user_name']) : ''; //用户名
$user_password = isset($_POST['user_password']) ? \basic\Basic::filterStr($_POST['user_password']) : ''; //用户密码
$user_password_iv = isset($_POST['iv']) ? \basic\Basic::filterStr($_POST['iv']) : ''; //iv 解密用

$_code = isset($_POST['code']) ? \basic\Basic::filterStr(strtolower($_POST['code'])) : ''; //验证码

$user_mobile = isset($_POST['user_mobile']) ? \basic\Basic::filterStr($_POST['user_mobile']) : ''; //注册手机号
$mobile_code = isset($_POST['mobile_code']) ? \basic\Basic::filterStr($_POST['mobile_code']) : ''; //手机验证码

// 验证必要字段
if (empty($regist_type) || (empty($user_name) && empty($user_mobile))) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再登录');
    exit;
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    switch ($regist_type) {
        case 1:
            $real_mobile_code = $_SESSION["mobile_code"]; //获取发送的验证码
            //判断验证码是否有误
            if (!($mobile_code === $real_mobile_code)) throw new Exception('验证码错误！');

            //判断当前手机号有没有注册过
            $sql_m = "select * from user where user_mobile=? and state='1'";
            //准备SQL语句
            $stmt_m = $link->prepare($sql_m);
            if (!$stmt_m) throw new Exception('手机号登录，SQL准备失败：' . $link->error);
            //绑定参数
            if (!($stmt_m->bind_param('s', $user_mobile))) throw new Exception('手机号登录，绑定参数失败：' . $link->error);
            //执行语句
            if (!$stmt_m->execute()) throw new Exception('手机号登录，SQL执行失败：' . $link->error);

            //获取查询结果集
            $result = $stmt_m->get_result();
            if (!($result->num_rows === 1)) {
                unset($_SESSION['mobile_code']); //销毁之前的验证码session值
                throw new Exception('该账号不存在!');
            }

            //获取用户信息
            $user_info = $result->fetch_assoc();
            $_SESSION['u_mobile'] = $user_mobile; //登录成功将用户名存入session
            $_SESSION['u_power'] = $user_info['power']; //登录成功将用户权限id存入session
            unset($_SESSION['mobile_code']); //销毁之前的验证码session值
            break;
        case 2:
            $real_code = strtolower($_SESSION['rel_captcha']); //获取实际验证码
            //判断验证码是否有误
            if (!($_code === $real_code)) throw new Exception('验证码错误！');
            //解密密码
            $password_decrypt_result = decryptIndex($user_password, $user_password_iv);
            if (!$password_decrypt_result['success']) throw new Exception('密码解密失败: '.$password_decrypt_result['error']);
            $user_password = $password_decrypt_result['data'];

            $user_password = my_crypt($user_password, 1);
            $sql_u = "select * from user where user_name=? and user_password=? and state='1'"; //判断当前账号有没有注册过
            //准备SQL语句
            $stmt_u = $link->prepare($sql_u);
            if (!$stmt_u) throw new Exception('用户名登录，SQL语句准备失败：' . $link->error);
            //绑定参数
            if (!($stmt_u->bind_param('ss', $user_name, $user_password))) throw new Exception('用户名登录，绑定参数失败：' . $link->error);
            //执行语句
            if (!$stmt_u->execute()) throw new Exception('用户名登录失败：' . $link->error);

            //获取查询结果集
            $result = $stmt_u->get_result();
            if (!($result->num_rows === 1)) {
                unset($_SESSION['rel_captcha']); //销毁之前的验证码session值
                throw new Exception('账号或密码有误，登录失败!');
            }

            //获取用户信息
            $user_info = $result->fetch_assoc();
            unset($_SESSION['rel_captcha']); //销毁之前的验证码session值
            $_SESSION['u_name'] = $user_name; //登录成功将用户名存入session
            $_SESSION['u_power'] = $user_info['power']; //登录成功将用户权限id存入session
            break;
        default:
            throw new Exception('无效的登录请求！');
            break;
    }

     // 设置用户token
    $_SESSION['user_token'] = $regist_type == 1 ? my_crypt($user_mobile, 1) : my_crypt($user_name, 1);
    
    // 更新最后登录时间
    if ($user_info && isset($user_info['id'])) {
        $update_sql = "UPDATE user SET last_login_time = ? WHERE id = ?";
        $update_stmt = $link->prepare($update_sql);
        if ($update_stmt) {
            $current_time = date('Y-m-d H:i:s');
            $update_stmt->bind_param('si', $current_time, $user_info['id']);
            $update_stmt->execute();
        }
    }

    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt_m) && $stmt_m instanceof mysqli_stmt) {
        $stmt_m->close();
    }
    if (isset($stmt_u) && $stmt_u instanceof mysqli_stmt) {
        $stmt_u->close();
    }
    if (isset($update_stmt) && $update_stmt instanceof mysqli_stmt) {
        $update_stmt->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
