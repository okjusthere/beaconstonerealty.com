<?php
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

//判断后台用户权限
if (!my_power("file_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据----要删除的文件所在路径组成的数组（包含文件名）

$file_ary = array(); //删除了哪些文件
$res = 0;
foreach ($data as $key => $value) {
    if (delete_files($value) == 0) {
        $file_ary[] = $value;
    } else {
        $res++;
    }
}

function delete_files($dir): int
{
    $state = 0;
    if (file_exists($dir)) { //检测要删除的文件是否存在
        if (!unlink($dir)) { //删除失败
            $state = 1;
        }
    } else {
        $state = 1;
    }

    return $state;
}

//关闭数据库链接
mysqli_close($link);

if(count($file_ary) > 0){
    $files = implode(',', $file_ary);
    updatelogs("批量删除文件，文件名：" . $files);
}

if ($res == 0) {
    echo $jsonData->jsonSuccessData($obj);
} else {
    $code = 100;  //响应码
    $message = 'error';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
}
