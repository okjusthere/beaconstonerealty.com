<?php
//中文处理
header('Content-type:text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*');//允许所有来源访问
header('Access-Control-Allow-Method:POST,GET');//允许访问的方式

//设置时区为中国时区，用Asia/Shanghai或者PRC
date_default_timezone_set('PRC');
//连接初始化（优先读取环境变量，用于 Railway 部署）
$host_name = getenv('DB_HOST') ?: 'localhost'; //主机地址
$user_name = getenv('DB_USER') ?: 'beaconstonerealt'; //数据库用户名
$password = getenv('DB_PASS') ?: 'M18tGayn7Jjb643s'; //数据库密码
$database = getenv('DB_NAME') ?: 'beaconstonerealt'; //数据库名称
$db_port = getenv('DB_PORT') ?: 3306; //数据库端口
@$link = mysqli_connect($host_name, $user_name, $password, $database, $db_port);
if (!$link) {
    die('数据库链接失败！');
}

/*//封装MySQL语法错误检查函数并执行
/*
 * @param1 string $sql，要执行的SQL指令
 * @return $res，正确执行完返回的结果，如果SQL错误，直接终止
 * */
function my_sql($sql)
{
    //定义$link为全局变量，便于引用
    global $link;

    //执行SQL
    $res = mysqli_query($link, $sql);

    //处理可能存在的错误
    if (!$res) {
        /*echo 'SQL执行错误，错误编号为：' . mysqli_errno($link) . '<br/>';
        echo 'SQL执行错误，错误信息为：' . mysqli_error($link) . '<br/>';*/
        echo 500;
        //出错后终止继续执行
        exit();
    }

    //返回结果
    return $res;
}

//字符集处理
my_sql('set names utf8mb4');
