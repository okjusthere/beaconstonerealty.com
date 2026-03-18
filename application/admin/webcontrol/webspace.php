<?php
//获取网站空间信息
//查看是否有访问权限
include_once '../checking_user.php';

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$data = file_get_contents('../config/webpackage.json');
$data = json_decode($data, true);

$data['webpackage'] = my_crypt($data['webpackage'], 2); // 解密网站版本
$data['spacesize'] = (int)my_crypt($data['spacesize'], 2); // 解密网站空间大小

$spacesize = $data['spacesize'] * 1024 * 1024 * 1024; // 将总空间大小，由GB转换成B
$usesize = dirsize('../../../'); // 获取当前网站使用了多少空间（单位字节）
$data['usespace'] = transf_byte($usesize); // 已使用空间大小，将上面获得的实际空间大小进行单位转换，以合适的单位展现出来

$emptysize = $spacesize - $usesize; // 用总空间大小，减去已使用空间大小，获得剩余空间大小（单位字节）
$data['emptyspace'] = transf_byte($emptysize); // 剩余空间大小，将上面获取的剩余空间大小进行单位换算，以合适的单位展现出来

$data['usagerate'] = round($usesize / $spacesize * 100); // 空间使用率

$obj['data'] = $data;

//$_SERVER['DOCUMENT_ROOT']; //获取站点根目录
//计算站点所用空间大小
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

//传入字节单位，进行单位换算
function transf_byte($byte)
{
    //换算
    $KB = 1024;
    $MB = $KB * 1024;
    $GB = $MB * 1024;
    $TB = $GB * 1024;

    if ($byte < $KB) {
        return $byte . 'B';
    } else if ($byte < $MB) {
        //取两位小数四舍五入
        return round($byte / $KB, 2) . ' KB';
    } else if ($byte < $GB) {
        return round($byte / $MB, 2) . ' MB';
    } else if ($byte < $TB) {
        return round($byte / $GB, 2) . ' GB';
    } else {
        return round($byte / $TB, 2) . ' TB';
    }
}

echo $jsonData->jsonData($code, $message, $obj);
