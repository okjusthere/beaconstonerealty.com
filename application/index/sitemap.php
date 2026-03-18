<?php
//获取嵌入代码信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$html = isset($_POST['html']) ? $_POST['html'] : ''; //获取前端传过来的源码
$site = isset($_POST['site']) ? $_POST['site'] : ''; //获取前端传过来的源码

$path = "../sitemap.txt"; //网站地图文件路径

if (!empty($html) && $site === "update") {
    // 创建 DOM 解析器
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    //var_dump($dom->loadHTML($html));

    // 获取所有链接
    $links = $dom->getElementsByTagName('a');
    $href_ary = array(); //定义一个空数组，用来放获取到的链接

    // 遍历所有链接并输出它们的地址
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if (filterateUrl($href) == 0) {
            $href_ary[] = $href;
        }
    }

    if (count($href_ary) > 0) {
        $href_ary = array_unique($href_ary); //去除数组中重复的链接
        $href_ary = array_values($href_ary); //对去重后的数组进行重新排列

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://'; //获取当前协议头
        $host = $_SERVER['HTTP_HOST'] . '/'; //获取当前网址

        $sitemap = $protocol . $host . "\n";
        // 将获取的链接进行拼接，生成网站地图（sitemap.txt）
        foreach ($href_ary as $href) {
            if (strpos($href, '&') !== false) {
                $href = str_replace('&', '&amp;', $href); //对含有&符号的链接进行转换，不然前端访问sitemap.xml会有问题
            }
            $sitemap .= $protocol . $host . $href . "\n";
        }

        $res = file_put_contents($path, $sitemap); //将内容写入指定文件
        if ($res > 0) {
            $code = 200;  //响应码
            $message = 'success';  //响应信息
        }
    }
} else {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}

/*
 * 判断链接中，是否有不符合条件的链接
 * @param string */
function filterateUrl($url)
{
    $num = 0;
    if (strpos($url, 'http:') !== false) $num++;
    if (strpos($url, 'https:') !== false) $num++;
    if (strpos($url, 'tel:') !== false) $num++;
    if (strpos($url, 'mailto:') !== false) $num++;

    return $num;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
