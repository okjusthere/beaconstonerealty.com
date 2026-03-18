<?php
//编辑图片
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

$id = (int)$data->id; //图片ID
$paixu = $data->paixu; //图片排序
$classid = empty($data->classid) ? 0 : $data->classid; //图片所属位置ID
$path = count($data->path) == 0 ? '' : json_encode($data->path, JSON_UNESCAPED_UNICODE); //缩略图
$name = $data->name; //图片名称（不再使用addslashes）
$url = $data->url; //图片链接地址
$remarks = $data->remarks; //图片描述（不再使用addslashes）
$width = $data->width; //建议尺寸（宽）
$height = $data->height; //建议尺寸（高）

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("pic_edit")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 使用预处理语句
$sql = "UPDATE pic_info SET 
        classid = ?, 
        name = ?, 
        path = ?, 
        url = ?, 
        remarks = ?, 
        paixu = ?, 
        width = ?, 
        height = ? 
        WHERE id = ?";

$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    $stmt->bind_param(
        'issssiiii', 
        $classid, 
        $name, 
        $path, 
        $url, 
        $remarks, 
        $paixu, 
        $width, 
        $height, 
        $id
    );
    
    // 执行预处理语句
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $code = 200;
        $message = 'success';
        // updatelogs("图片管理，修改图片信息，ID：" . $id); //记录操作日志
    } else {
        $code = 100;
        $message = 'error';
    }
} else {
    $code = 100;
    $message = '数据库预处理失败';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);