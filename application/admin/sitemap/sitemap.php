<?php
//获取文章表信息--页面
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://'; //获取当前协议头
$host = $_SERVER['HTTP_HOST'] . '/'; //获取当前网址

$url = $protocol . $host . PHP_EOL;
// SQL语句
$sql = "SELECT static_url FROM tb_rewrite_rules";
$stmt = $link->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $url .= $protocol . $host . $row['static_url'] . PHP_EOL;
}
//关闭语句
$stmt->close();

$filename = '../../../sitemap.txt'; //网站地图文件

$res = file_put_contents($filename, $url); //将内容写入指定文件
if ($res > 0) {
    $code = 200;
    $message = 'success';
} else {
    $code = 100;
    $message = '没有写入权限，请联系管理员！';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);