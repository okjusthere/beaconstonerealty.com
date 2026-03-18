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
if (!my_power("file_download")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
//var_dump($data);
$file_name = $data->name;//需要下载的文件
$file_path = $data->directory;//文件路径（包含文件名）
$file_name = iconv("utf-8", "gb2312", "$file_name"); //将字符串按照指定的编码进行转换
$fp = fopen($file_path, "r");//下载文件必须先要将文件打开，写入内存
if (!file_exists($file_path)) {//判断文件是否存在
    echo "文件不存在";
    exit();
}
$file_size = filesize($file_path);//判断文件大小

//返回的文件
Header("Content-type: application/octet-stream");
//按照字节格式返回
Header("Accept-Ranges: bytes");
//返回文件大小
Header("Accept-Length: " . $file_size);
//弹出客户端对话框，对应的文件名
Header("Content-Disposition: attachment; filename=" . $file_name);
//防止服务器瞬时压力增大，分段读取
$buffer = 1024;
while (!feof($fp)) {
    $file_data = fread($fp, $buffer);
//  echo $file_data;
//    var_dump($file_data);
//    $obj["data"] = $file_data;
//    $obj["data"] = base64_encode($file_data);
}
$obj["data"] = binaryEncodeImage($file_path);
function binaryEncodeImage($img_file) {
    $p_size = filesize($img_file);
    $img_binary = fread(fopen($img_file, "r"), $p_size);
    return $img_binary;
}

//    var_dump($obj);


//关闭文件
fclose($fp);

if (!empty($obj)) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}


//var_dump($jsonData->jsonData($code, $message, ));
//$obj = mb_convert_encoding($obj, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

//$content = ['code' => $code, 'message' => $message, 'obj' => $obj];
////var_dump($content);
////var_dump(json_encode($obj));
//echo json_encode($content);

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
//echo json_last_error_msg();

