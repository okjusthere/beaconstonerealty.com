<?php
//插入提交表单
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

include_once "function.php"; //引用自定义函数

// 导入调用参数的方法
require_once('../../myclass/Parameter.php');
require_once('../../myclass/Email.php'); //邮件发送接口
//require_once('../../myfunction/functionsms.php'); //短信发送接口

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

header('Content-Type:application/json; charset=utf-8');

$title = isset($_POST['title']) ? \basic\Basic::filterStr($_POST['title']) : ''; //咨询主题
$contacts = isset($_POST['contacts']) ? \basic\Basic::filterStr($_POST['contacts']) : ''; //联系人
$phone = isset($_POST['phone']) ? basic\Basic::filterStr($_POST['phone']) : ''; //联系电话
$_email = isset($_POST['email']) ? basic\Basic::filterStr($_POST['email']) : ''; //电子邮箱
$_message = isset($_POST['message']) ? basic\Basic::filterStr($_POST['message']) : ''; //咨询内容
$_code = isset($_POST['code']) ? basic\Basic::filterStr(strtolower($_POST['code'])) : ''; //验证码
$source = isset($_POST['source']) ? basic\Basic::filterStr(strtolower($_POST['source'])) : ''; //留言来源（用来判断留言来自前端哪个页面{inquiry：产品询盘，为空时是单纯的客户留言页面}）
$add_time = time(); //提交时间
$ip = $_SERVER["REMOTE_ADDR"]; //提交IP

// $rel_captcha = strtolower($_SESSION['rel_captcha']); //获取当前实际验证码
if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
$rel_captcha = strtolower($_SESSION['mobile_code']); //获取当前实际验证码-手机短信验证码

if ($_code === $rel_captcha) {
    //插入客户留言信息表
    $sql = "insert into message (title, name, phone, email, message,state,add_time, ip) values ('{$title}','{$contacts}','{$phone}','{$_email}','{$_message}','1','{$add_time}','{$ip}')";
    $res = my_sql($sql);
    if ($res) {
        $r_id = mysqli_insert_id($link); //返回当前插入查询表记录的ID

        $code = 200;  //响应码
        $message = 'success';  //响应信息

        //提交成功，给网站相关人员发送短信和邮件，告知有客户提交了留言
        // 使用示例
        /*$phoneNumber = $param::getParameter('phoneNumber');  // 替换成目标手机号
        $email_number = $param::getParameter('email');  // 目标邮箱
        $canSendSMS = canSendSMS(); //查看是否有发送短信的余量
        if (!empty($phoneNumber) && $canSendSMS) {
            if ($source == "inquiry") {
                $templateCode = 'SMS_464340114';  // 替换成短信模板CODE
                $templateParamArray = ['number' => $r_id];  // 替换成短信模板中的参数，以数组形式传递
                $response = sendSMS($phoneNumber, $templateCode, $templateParamArray);
                if ($response["Message"] == "OK") { //短信发送成功，扣除短信余量
                    updateNumberSMS(); //减去短信余量
                }
            }
        }
        if (!empty($email_number)) {
            $email_title = "产品询盘通知";
            if ($source == "inquiry") {
                $info = '您有新的产品询盘信息，请前往官网后台【管理中心】→【客户留言】查看，编号：' . $r_id;
            } else {
                $info = '您有新的客户留言，请前往官网后台【管理中心】→【客户留言】查看，编号：' . $r_id;
                $email_title = "在线留言通知";
            }
            $send_state = $email_class::sendinfo($email_number, $email_title, $info); //发送邮件
        }*/
    } else {
        $code = 100;  //响应码
        $message = '出错了，请联系管理员！';  //响应信息
    }
} else {
    $_SESSION['rel_captcha'] = mt_rand(10000, 99999); //销毁之前的验证码session值
    $code = 100;  //响应码
    $message = '验证码有误，请重试！';  //响应信息
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
