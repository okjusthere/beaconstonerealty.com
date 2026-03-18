<?php
$network_security = true; //网络安全开关状态-默认开启
$sql_check = "SELECT network_security FROM web_control LIMIT 1"; //查询当前网络安全开关状态
$res_check = my_sql($sql_check);
if ($res_check && mysqli_num_rows($res_check) > 0) {
    $network_security = !(mysqli_fetch_assoc($res_check)["network_security"] == '2');
}
if ($network_security) {
    //检查是否是ajax请求
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        http_response_code(403);
        die();
    }
}
//禁止在接口后面随便拼接字符串，否则直接无法获取接口数据
if ($_SERVER['SCRIPT_NAME'] !== $_SERVER['REQUEST_URI']) {
    http_response_code(403);
    die();
}