<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
$php_self = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1); //获取当前页面名称（无视目录层级）
$no_check_page = ["controller.php", "getbasicinfo.php"];

if ((empty($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) && !in_array($php_self, $no_check_page)) {
    http_response_code(403);
    die();
}
if (!preg_match('#^' . preg_quote($_SERVER['SCRIPT_NAME'], '#') . '(?:\?[^\?]*)?$#', $_SERVER['REQUEST_URI'])) {
    http_response_code(403);
    die();
}

if ($php_self !== "login.php" && $php_self !== "login_auto.php" && $php_self !== "logout.php" && $php_self !== "sendcode.php" && $php_self !== "getbasicinfo.php") {
    $sessin_token = $_SESSION["manager"] ?? ''; //获取session值
    if ($php_self === "controller.php") {
        if (empty($sessin_token)) {
            http_response_code(403);
            die();
        }
    } else {
        $header_token = $_SERVER['HTTP_AUTHORIZATION']; //获取请求头里“AUTHORIZATION”的值
        if ($sessin_token !== $header_token || empty($sessin_token)) {
            $_SESSION = []; // 清空所有 session 数据
            session_destroy(); // 销毁 session 文件
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, '', time() - 3600, "/");
                setcookie($key, '', time() - 3600, "/", $_SERVER["HTTP_HOST"], false, true);
            }
            echo json_encode(['code' => 300, 'message' => 'login out', 'obj' => []]);
            exit();
        }
    }
}

/*检查用户权限
 * 参数$power：要判断的权限名称*/
function my_power($power)
{
    $state = false;
    $user = my_crypt($_COOKIE['AdminName'], 2); //解密用户名
    if ($user !== "13812345678") {
        $sql = "select power from admin_power where id=(select power from admin where username='{$user}')"; //查找用户权限
        $res = my_sql($sql);
        if ($res) {
            $powerAry = json_decode(mysqli_fetch_assoc($res)['power']);

            $state = in_array($power, $powerAry); //判断用户是否有对应权限
        }
    } else {
        $state = true;
    }
    return $state;
}

/* 加密/解密
 * 参数$data：要加密/解密的内容--加密明文/解密密文
 * 参数$type：要加密还是解密（1：加密；2：解密）*/
function my_crypt($data, int $type)
{
    $method = 'aes-128-ecb'; //加密/解密方式，可以通过openssl_get_cipher_methods()获取有哪些加密方式
    $key = 'webforce'; //秘钥
    $options = 0; //以下标记的按位或： OPENSSL_RAW_DATA 、 OPENSSL_ZERO_PADDING

    if ($type === 1) {
        return openssl_encrypt($data, $method, $key, $options);
    } else if ($type === 2) {
        return openssl_decrypt($data, $method, $key, $options);
    }
}

/*更新操作日志
 *@param string $event 操作事件*/
function updatelogs($event)
{
    //检查是否启用了session会话，没有启用的话启用
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $manager_mobile = isset($_SESSION["manager_mobile"]) ? my_crypt($_SESSION["manager_mobile"], 2) : ""; //获取当前用户名，可能是手机号登录的
    $manager_username = isset($_SESSION["manager_username"]) ? my_crypt($_SESSION["manager_username"], 2) : ""; //获取当前用户名，可能是用户名登录的

    $admin_name = "login_error"; //操作用户
    if (!empty($manager_mobile)) {
        $admin_name = $manager_mobile;
    } else if (!empty($manager_username)) {
        $admin_name = $manager_username;
    }
    $operate_time = time(); //操作时间
    $ip = $_SERVER["REMOTE_ADDR"]; //操作IP

    $sql = "insert into tb_logs (admin_name, event, ip_address, operate_time) values ('{$admin_name}','{$event}', '{$ip}', '{$operate_time}')";

    my_sql($sql); //执行操作日志插入语句
}