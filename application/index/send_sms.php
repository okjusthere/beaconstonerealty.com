<?php
//获取网站开关信息
include_once '../../wf-config.php'; //链接数据库

include_once "function.php"; //引用自定义函数

// 导入调用参数的方法
require_once('../../myclass/Parameter.php');
require_once('../../myclass/Email.php'); //邮件发送接口
require('../../myfunction/functionsms.php'); //调用发送接口
include_once '../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$param = new \myclass\Parameter(); //获取相关参数
$email_class = new \myclass\Email();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

//中文处理
header('Content-Type:application/json; charset=utf-8');

$type = isset($_POST['type']) ? $_POST['type'] : 0; //短信类型，用来判断调用哪个发送接口
$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : ''; //接收短信的手机号

$result = false; //短信发送状态{false：发送失败；true：发送成功}
//根据类型，选择对应的短信接口
if (!empty($mobile)) {
    switch ((int)$type) {
        case 1:
            $result = sendRegisterCode($mobile);
            break;
        default:
            return '短信接口无法调用';
    }
}

//发送成功扣除短信
if ($result) {
    $code = 200;  //响应码
    $message = 'success';  //响应信息
}

/*
 *发送用户注册验证码
 *@param $mobile string 要接收短信的手机号（多个手机号用英文逗号','隔开）
*/
function sendRegisterCode($mobile)
{
    //生成随机验证码
    $num = '123456789082739472957026';
    $num = str_shuffle($num);
    $num = substr($num, -6);
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $_SESSION['mobile_code'] = $num; //将验证码存入session

    // 使用示例
    $phoneNumber = $mobile;  // 替换成目标手机号
    $templateCode = 'SMS_485335412';  // 替换成短信模板CODE
    $templateParamArray = ['code' => $num];  // 替换成短信模板中的参数，以数组形式传递

    $response = sendSMS($phoneNumber, $templateCode, $templateParamArray);

    if ($response['Code'] == 'OK') {
        updateNumberSMS(); //减去短信余量
        return true;
    } else {
        //return 'error:' . $response['Message'];
        return false;
    }
}

echo $jsonData->jsonData($code, $message, $obj);