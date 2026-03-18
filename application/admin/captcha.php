<?php
//制作验证码图片

//创建图片资源
$img = imagecreatetruecolor(90, 40);

//背景色
$bg_color = imagecolorallocate($img, 220, 220, 220);
imagefill($img, 0, 0, $bg_color);

//获得随机大小写英文+数字
$str = 'qwertyuiopasdfghjkmnbvcxzPLOKIJMUHNBGYTFVCDREWSXZAQ23456789';
//获取字符串长度
$length = strlen($str);
$con1 = substr($str, mt_rand(0, $length - 1), 1);
$con2 = substr($str, mt_rand(0, $length - 1), 1);
$con3 = substr($str, mt_rand(0, $length - 1), 1);
$con4 = substr($str, mt_rand(0, $length - 1), 1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session

//将实际验证码写入SESSION
$_SESSION['rel_captcha'] = "{$con1}{$con2}{$con3}{$con4}";

// 立即保存session数据
session_write_close();

//将验证码写入cookie中  "/"斜杠的目的是为了让cookie在整个项目里有效
//setcookie('rel_captcha', $con1 . $con2 . $con3 . $con4, time() + 300, '/', $_SERVER['HTTP_HOST'], false, true);
//header('Set-Cookie:rel_captcha=' . $con1 . $con2 . $con3 . $con4 . ';path=/;domain=' . $_SERVER['HTTP_HOST'] . ';HttpOnly');

//写入内容
//调用字体
$font = dirname(__FILE__) . '/font/CENTURY.TTF';   //由于GD版本更新，定义字体路径参数需要使用绝对路径。
imagettftext($img, font_size_rand(), angle_rand(), 8, y_rand(), color_rand(), $font, $con1);
imagettftext($img, font_size_rand(), angle_rand(), 26, y_rand(), color_rand(), $font, $con2);
imagettftext($img, font_size_rand(), angle_rand(), 48, y_rand(), color_rand(), $font, $con3);
imagettftext($img, font_size_rand(), angle_rand(), 70, y_rand(), color_rand(), $font, $con4);

//干扰：干扰点、干扰线
//干扰点
for ($i = 0; $i < 5; $i++) {
    //点：写字符
    imagestring($img, mt_rand(1, 5), mt_rand(0, 90), mt_rand(0, 40), symbol_rand(), color_rand());
}
//干扰线
for ($i = 0; $i < 3; $i++) {
    //线：画直线
    imageline($img, mt_rand(0, 90), mt_rand(0, 40), mt_rand(0, 90), mt_rand(0, 40), color_rand());
}

//输出资源
header('Content-type:image/png;');  //设定输出格式
imagepng($img); //png格式图片是透明的，比较小

//销毁资源
imagedestroy($img);

//获取随机颜色
function color_rand()
{
    global $img;
    return imagecolorallocate($img, mt_rand(0, 0), mt_rand(165, 165), mt_rand(229, 229));
}

//获取随机符号
function symbol_rand()
{
    $str = '!,/.#%^*';
    return substr($str, mt_rand(0, 8), 1);
}

//获取随机字体大小
function font_size_rand()
{
    return mt_rand(18, 22);
}

//获取随机角度
function angle_rand()
{
    return mt_rand(-15, 15); //角度一般不超过45度
}

//获取随机Y轴位置
function y_rand()
{
    return mt_rand(25, 35);
}
