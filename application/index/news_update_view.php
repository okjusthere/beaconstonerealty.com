<?php
//更新文章信息

//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$id = isset($_POST['id']) ? \basic\Basic::filterInt($_POST['id']) : ''; //文章ID
$value = isset($_POST['value']) ? \basic\Basic::filterInt($_POST['value']) : 0; //要更新的字段值
$field = "view"; //要更新的字段

$code = 500;  //响应码
$message = '更新失败！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$value = $value + 1; //阅读量+1
//sql查询语句
$sql = "update news set {$field}='{$value}' where id={$id}";

//获取查询结果集
$res = my_sql($sql);

if ($res) {
    $code = 200;
    $message = 'success';
} else {
    $code = 100;
    $message = '出错了，请联系管理员！';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
