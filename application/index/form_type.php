<?php
//获取新闻表信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//引用自定义函数
include_once "function.php";

//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$basic = new \basic\Basic();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$id = isset($_POST['id']) ? $basic::filterInt($_POST['id']) : 0; //表单分类ID

$where = "";
if ($id > 0) {
    //sql查询语句
    $sql = "select id,field from tb_form_type where state='1' and id={$id}";
    //获取查询结果集
    $res = my_sql($sql);
    if ($res) {
        $code = 200;
        $message = '查询成功！';
        $obj["data"] = array();
        if (mysqli_num_rows($res) > 0) {
            $obj["data"] = json_decode(mysqli_fetch_assoc($res)["field"]);
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
