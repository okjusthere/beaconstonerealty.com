<?php
//图片位置--编辑和添加图片的时候要用
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

header('Content-Type:application/json; charset=utf-8');

//sql查询语句
$sql = 'select * from pic_position';

//获取查询结果集
$res = my_sql($sql);

if ($res != '500') {
    $code = 200;
    $message = '查询成功！';
    $obj["data"] = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $info = ["value" => $row["id"], "label" => $row["name"]];
        $obj["data"][] = $info;
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
