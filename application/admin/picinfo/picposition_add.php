<?php
//添加图片位置
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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

$name = $data->name; //文章标题

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("pic_position_add")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 使用预处理语句
$sql = "INSERT INTO pic_position (name) VALUES (?)";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param('s', $name);
    
    // 执行预处理语句
    $result = $stmt->execute();
    
    if ($result) {
        $new_id = $stmt->insert_id; //获取最后插入的ID
        $code = 200;
        $message = 'success';
        updatelogs("图片管理，添加图片位置，ID：" . $new_id); //记录操作日志
    } else {
        $code = 100;
        $message = 'error';
    }
    
    // 关闭预处理语句
    $stmt->close();
} else {
    $code = 100;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);