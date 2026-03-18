<?php
//header('Content-Type:application/json; charset=utf-8'); //返回json
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

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

// 确保session启动
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 1. 首先清空SESSION数据
$_SESSION = []; // 清空所有 session 数据

// 2. 如果要彻底销毁session，删除session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3.销毁 session 文件
session_destroy();

// 4. 立即重新启动一个新的session用于后续验证码（重要！）
session_start();

// 5. 删除其他cookies（保持你的原有逻辑）
$del = 0;
foreach ($_COOKIE as $key => $value) {
    // 跳过session cookie，因为上面已经处理过了
    if ($key === session_name()) {
        continue;
    }
    setcookie($key, '', time() - 3600, "/");
    setcookie($key, '', time() - 3600, "/", $_SERVER["HTTP_HOST"], false, true);
    $del++;
}

if ($del > 0) { //删除成功
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}


//返回json
echo $jsonData->jsonData($code, $message, $obj);
