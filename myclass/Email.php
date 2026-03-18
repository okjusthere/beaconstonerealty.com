<?php

namespace myclass;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once(dirname(__DIR__) . '/myclass/PHPMailer/PHPMailer.php');
require_once(dirname(__DIR__) . '/myclass/PHPMailer/Exception.php');
require_once(dirname(__DIR__) . '/myclass/PHPMailer/SMTP.php');
// 导入调用参数的方法
require_once(dirname(__DIR__) . '/myclass/Parameter.php');

class Email
{
    /*
     * 发送邮件
     * @param string/array $receive_email 接收邮件的邮箱
     * @param string $title 邮箱标题
     * @param string $info 发送内容*/
    public static function sendinfo($receive_email, string $title, string $info)
    {
        $param = new \myclass\Parameter(); //获取相关参数

        $send_result = false; //邮件发送状态，默认失败（false）
        $mail = new PHPMailer(true);  // Passing `true` enables exceptions 传递“true”可启用异常
        try {
            //服务器配置
            $mail->CharSet = "UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                         // 调试模式输出
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;     // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = 'ssl://smtpdm.aliyun.com';     // SMTP服务器 邮件推送服务器
            $mail->SMTPAuth = true;                      // 授权（允许 SMTP 认证）
            $mail->Username = $param::getParameter('mailUsername');    // SMTP 用户名  即邮箱的用户名/发信地址
            $mail->Password = $param::getParameter('mailPassword');    // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // 启用加密（允许 TLS 或者ssl协议）
            $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持（godaddy的端口得用80，官方给的465不对）
            $mail->setFrom($param::getParameter('mailUsername'), $param::getParameter('mailSetFromName'));   //发件人（显示发信地址,可设置代发。）
            //Recipients
            if(strpos($receive_email,',')!==false){ //判断邮件是否有英文逗号，如果有，将邮件转换为数组
                $receive_email=explode(',',$receive_email);
            }
            if (is_array($receive_email)) {
                foreach ($receive_email as $value) {
                    $mail->addAddress($value);
                }
            } else {
                $mail->addAddress($receive_email);     //收件人和昵称
            }
            //$mail->addAddress('test1***@example.net');               //昵称也可不填
            //$mail->addAddress('test2***@example.net', 'name2'); //如果需要设置多个收件人，可以再添加一条
            $mail->addReplyTo($param::getParameter('mailUsername'), 'Information'); //回信地址（回复的时候回复给哪个邮箱 建议和发件人一致）
            $mail->addCC('test***@example.net'); //抄送人
            $mail->addBCC('test***@example.net'); //密送人

            //发送附件
            // $mail->addAttachment('../xy.zip');         //添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    //发送附件并且重命名

            //Content
            $mail->isHTML(true);  //是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = $title; //邮件标题
            $mail->Body = $info . '<br>【系统邮件请勿回复】'; //邮件内容
            $mail->AltBody = $info . '【系统邮件请勿回复】';  //如果邮件客户端不支持HTML则显示此内容

            $mail->Sender = $param::getParameter('mailUsername');

            if ($mail->send()) {
                $send_result = true;
            }
        } catch (Exception $e) {
            // echo '邮件发送失败: ', $mail->ErrorInfo;
            // $send_result = true;
        }

        return $send_result;
    }
}





