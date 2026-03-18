<?php
//获取网站开关信息
include_once '../../wf-config.php'; //链接数据库
global $link;
include_once 'check.php';
include_once "../common.php"; //引用常量
include_once "function.php"; //引用自定义函数
include "basic.php"; //引用全局参数

//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//获取网站开关状态
$webState = getWebControl($link);
$obj["data"]["web_control"] = $webState;

//通过接口获取网站到期时间
@$webinfo = file_get_contents(API_WEBINFO . $_SERVER['HTTP_HOST']);
$web_info = json_decode($webinfo, true);
if (isset($web_info["obj"]["data"]["start_time"]) && isset($web_info["obj"]["data"]["end_time"])) {
    $current_timestamp = time(); // 获取当前时间戳
    $end_time = $web_info["obj"]["data"]["end_time"]; // 网站到期时间戳
    // 如果网站已超时，并且网站不是关闭状体，关闭网站
    if (($current_timestamp > $end_time) && $webState["state"] == '1') {
        $tips = "网站于" . date("Y-m-d", $end_time) . "已经到期<br>请您尽快联系我们续费！<br>到期后，网站数据我们会为您免费保存3天，3天后数据将会被删除！<br>有任何疑问，请联系服务支持人员，谢谢！<br>售后QQ：97769061"; //到期提醒
        // 使用预处理语句更新数据
        $sql = "UPDATE web_control SET state='2',tips ='{$tips}' WHERE id=1";
        $stmt = $link->prepare($sql);
        $result = $stmt->execute();
        if ($result) {
            $obj["data"]["web_control"]["state"] = '2';
            $obj["data"]["web_control"]["tips"] = $tips;
        } else {
            $code = 300;
            $message = 'error';
        }
        $stmt->close();
    } else if (($current_timestamp < $end_time) && $webState["state"] == '2' && safeContainsChinese($webState["tips"], "续费")) {
        $tips = "网站维护中……请稍后访问"; //到期提醒
        // 使用预处理语句更新数据
        $sql = "UPDATE web_control SET state='1', tips='{$tips}' WHERE id=1";
        $stmt = $link->prepare($sql);
        $result = $stmt->execute();
        if ($result) {
            $obj["data"]["web_control"]["state"] = '1';
            $obj["data"]["web_control"]["tips"] = $tips;
        } else {
            $code = 300;
            $message = 'error';
        }
        $stmt->close();
    }
}

//当网站关闭时，不再请求其他数据
if ($obj["data"]["web_control"]["state"] == '1') {
    //$obj["data"]["web_seo_info"] = getWebSeoInfo($link);
    $obj["data"]["web_info"] = getWebInfo($link);
    $obj["data"]["web_code"] = getWebCode($link);
    $obj["data"]["pic_info"] = getPicInfo($link);
    $obj["data"]["menu_info"] = getMenuInfo($link);
    $obj["data"]["news_class_info"] = getNewsClassInfo($link);
    $obj["data"]["product_class_info"] = getProductClassInfo($link);
    $obj["data"]["customer_service"] = getCustomerService();
    $obj["data"]["links_info"] = getLinksInfo($link);
    $obj["data"]["links_class_info"] = getLinksClassInfo($link);
} else {
    $message = "网站已关闭，无法获取数据信息";
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
