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
require_once('../../myfunction/functionsms.php'); //短信发送接口

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$basic = new \basic\Basic();

$param = new \myclass\Parameter(); //获取相关参数
$email_class = new \myclass\Email();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$form_type_id = (int)$_POST["id"]; //表单分类ID
$add_time = time(); //提交时间
$ip = $_SERVER["REMOTE_ADDR"]; //提交IP

$field_title = ''; //表单名称
$field_info = array(); //获取到的字段信息

//通过表单分类ID获取该表单要获取的字段值
$sql_form_type = "select title,field,sms_notification,email_notification from tb_form_type where state='1' and id={$form_type_id}";
$res_form_type = my_sql($sql_form_type);
if ($res_form_type) {
    if (mysqli_num_rows($res_form_type) === 1) {
        $data = $row = mysqli_fetch_assoc($res_form_type);
        $field_title = $data['title']; //表单名称
        $field = json_decode($data['field'], true); //要获取的字段信息
        $sms_notification = $data['sms_notification'] === '1'; //是否开启短信通知
        $email_notification = $data['email_notification'] === '1'; //是否开启邮件通知

        foreach ($field as $value) {
            $info = [];
            $info["field_name"] = $value["field_name"]; //字段名称
            $info["field_description"] = $value["field_description"]; //字段描述
            $info["field_type"] = $value["field_type"]; //字段类型
            $info["field_content"] = $basic::filterStr($_POST[$value["field_name"]]); //前端提交信息（进行非法字符串过滤）
            $field_info[] = $info; //将获取到的值拼接起来
        }

        if (count($field_info) > 0) {
            $field_content = json_encode($field_info, JSON_UNESCAPED_UNICODE); //将数组转换为json字符串
            $sql = "insert into tb_form (type_id,content,add_time,ip) values ({$form_type_id},'{$field_content}','{$add_time}','{$ip}')";
            $res = my_sql($sql);
            if ($res) {
                $new_id = mysqli_insert_id($link); //返回当前插入查询表记录的ID
                $code = 200;  //响应码
                $message = 'success';  //响应信息

                //提交成功，给网站相关人员发送短信和邮件，告知有客户提交了留言
                // 使用示例
                $phoneNumber = $param::getParameter('phoneNumber');  // 替换成目标手机号
                $email = $param::getParameter('email');  // 目标邮箱
                $canSendSMS = canSendSMS(); //查看是否有发送短信的余量
                if ($sms_notification && !empty($phoneNumber) && $canSendSMS) {
                    $templateCode = 'SMS_464340114';  // 替换成短信模板CODE
                    $templateParamArray = ['number' => $new_id];  // 替换成短信模板中的参数，以数组形式传递
                    $response = sendSMS($phoneNumber, $templateCode, $templateParamArray);
                    if ($response["Message"] == "OK") { //短信发送成功，扣除短信余量
                        updateNumberSMS(); //减去短信余量
                    }
                }
                if ($email_notification && !empty($email)) {
                    $email_title = "新表单信息通知";
                    $info = '表单【' . $field_title . '】收到新信息，请前往<a href="' . getUrl() . '/wf-admin" target="_blank">官网后台</a>【管理中心】→【在线表单】查看，信息编号：' . $new_id;
                    $send_state = $email_class::sendinfo($email, $email_title, $info); //发送邮件
                }
            }
        }

    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
