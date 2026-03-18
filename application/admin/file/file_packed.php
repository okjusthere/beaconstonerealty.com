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
if (!my_power("file_packed")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$dir = "../../../";  //要读取的目录
$typearr = array('html', 'css', 'js', 'txt', 'jepg', 'jpg', 'png', 'gif');  //允许读取的文件类型以及目录
$file_list = bdir($dir, $typearr);

$zip = new ZipArchive(); //创建一个压缩包对象
$filename = $dir . "backup/" . $_SERVER['HTTP_HOST'] . "_html_" . date('YmdHis') . ".zip"; //给压缩包命名
//$filename = "test.zip"; //给压缩包命名
//打开一个文件句柄
if ($zip->open($filename, ZipArchive::CREATE) !== true) {
    exit("打开文件" . $filename . "失败！");
}

//对文件和目录进行打包
$result = 0;  //返回打包结果状态
if (count($file_list) > 0) {
    foreach ($file_list as $k => $v) {
        if ($v["type"] == 0) {  //将目录以及目录下的所有内容放到压缩包
            $res_packed_dir = $zip->addGlob($dir . "/" . $v["name"] . "/*", GLOB_BRACE, ["add_path" => $v["name"] . "/", 'remove_all_path' => true]);
            if (!$res_packed_dir) {
                $result .= $result++;
            }
        } else if ($v["type"] == 1) {  //将单个文件放到压缩包，加入已存在的文件(加第二个参数，可以修改打包后的文件名)
            $res_packed_file = $zip->addFile($dir . "/" . $v["name"], $v["name"]);
            if (!$res_packed_file) {
                $result .= $result++;
            }
        }
    }
}

//关闭打开的压缩包
$zip->close();


/*获取指定目录下相关内容
 *参数$dir：要读取的目录
 *参数$typearr：允许显示的文件类型以及目录
 */
function bdir($dir, $typearr)
{
    $res = array();
    $ndir = scandir($dir);
    foreach ($ndir as $k => $v) {
        //排除.和..
        if ($v == '.' || $v == '..') {
            continue;
        }
        if (filetype($dir . $v) == 'file') {

            $arr = explode('.', $v);  //使用一个字符串分割另一个字符串

            $type = end($arr);  //将数组的内部指针指向最后一个单元

            if (in_array($type, $typearr)) {
                $row_file["name"] = $v;  //文件名称
                $row_file["type"] = 1; //当是文件时，定义类型为1
                $res[] = $row_file;
            }
        } else if (filetype($dir . $v) == 'dir' && ($v == "css" || $v == "images" || $v == "js")) {
            $row_dir["name"] = $v;  //目录名称
            $row_dir["type"] = 0; //当是目录时，定义类型为0
            $res[] = $row_dir;
        }

    }

    return $res;
}

//关闭数据库链接
mysqli_close($link);

if ($result == 0) {
    echo $jsonData->jsonSuccessData($obj);
} else {
    $code = 100;  //响应码
    $message = 'error';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
}
