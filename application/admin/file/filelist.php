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
if (!my_power("file_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$dir_name = isset($_GET['dir']) ? $_GET['dir'] : '';  //要进入的目录名称

//$dir = $_SERVER['DOCUMENT_ROOT'] . "/";  //要读取的目录
$dir = "../../../";  //要读取的目录
$typearr = array('html', 'css', 'js', 'txt', 'jpeg', 'jpg', 'png', 'gif', 'ico', 'xml', 'php');  //允许显示的文件类型以及目录
if (!empty($dir_name)) {
    $dir = $dir . $dir_name . "/";
}
if ($dir_name == "backup") {  //当读取备份文件夹时，只访问压缩包类型的文件
    $typearr = array('zip');
}
$file_list = bdir($dir, $typearr);  //获取文件列表
if (count($file_list) > 0) {
    $code = 200;
    $message = 'success';
    $obj["data"] = $file_list;
} else if (count($file_list) == 0) {
    $code = 200;
    $message = 'none';
    $obj["data"] = [];
}

/*获取指定目录下相关内容
 *参数$dir：要读取的目录
 *参数$typearr：允许显示的文件类型以及目录
 */
function bdir($dir, $typearr)
{
    $res = array();
    $ndir = scandir($dir);
    $typearr_img = array('jpeg', 'jpg', 'png', 'gif', 'ico');  //允许显示的图片类型，用来区分文件类型是图片还是html文件或者是txt
    foreach ($ndir as $k => $v) {
        //排除.和..
        if ($v == '.' || $v == '..') {
            continue;
        }
        $path = $dir . $v;

        if (filetype($path) == 'file') {

            $arr = explode('.', $v);  //使用一个字符串分割另一个字符串

            $type = end($arr);  //将数组的内部指针指向最后一个单元

            if (in_array($type, $typearr)) {
                $row_file["name"] = $v;
                $row_file["size"] = trans_byte(filesize($path));
                $row_file["type"] = 1; //当是文件时，定义类型为1，图片的时候定义类型为2
                if (in_array($type, $typearr_img)) {
                    $row_file["type"] = 2;
                }
                $row_file["update"] = filemtime($path);  //文件修改时间
                $row_file["directory"] = $path;  //文件路径
                $res[] = $row_file;
            }
        } else if (filetype($path) == 'dir' && ($v == "css" || $v == "images" || $v == "js")) {
            $row_dir["name"] = $v;
            $row_dir["size"] = trans_byte(dirsize($path));
            $row_dir["type"] = 0; //当是目录时，定义类型为0
            $row_dir["update"] = filemtime($path);  //文件修改时间
            $row_dir["directory"] = $path;  //目录路径
            $res[] = $row_dir;
        }

    }

    return $res;
}

//定义计算文件大小的函数，以常见的格式显示
function trans_byte($byte)
{
    $KB = 1024;
    $MB = 1024 * $KB;
    $GB = 1024 * $MB;
    $TB = 1024 * $GB;
    if ($byte < $KB) {
        return $byte . 'B';
    } else if ($byte < $MB) {
        return round($byte / $KB, 2) . 'KB';
    } else if ($byte < $GB) {
        return round($byte / $MB, 2) . 'MB';
    } else if ($byte < $TB) {
        return round($byte / $GB, 2) . 'GB';
    } else {
        return round($byte / $TB, 2) . 'TB';
    }
}

//读取目录大小
function dirsize($dir)
{
    @$dh = opendir($dir);
    $size = 0;
    while ($file = @readdir($dh)) {
        if ($file != "." and $file != "..") {
            $path = $dir . "/" . $file;
            if (is_dir($path)) {
                $size += dirsize($path);
            } elseif (is_file($path)) {
                $size += filesize($path);
            }
        }
    }
    @closedir($dh);
    return $size;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
