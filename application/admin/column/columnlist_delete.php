<?php
//按钮删除栏目信息
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
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("column_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
$id = (int)$data->id;

if (!empty($id)) {
    //sql若没有子分类，执行删除
    $sql = 'delete from column_list where id=' . $id;
    $res = my_sql($sql);
    if ($res) {
        $code = 200;  //响应码
        $message = 'success';  //响应信息
        updatelogs("删除导航菜单，ID：" . $id); //记录操作日志
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
