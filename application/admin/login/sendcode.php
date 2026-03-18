<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 调用发送接口
require('../../myfunction/functionsms.php');

// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 获取用户输入的区号、手机号
$area_code = isset($data->area_code) ? trim($data->area_code) : '';
$mobile = isset($data->mobile) ? trim($data->mobile) : '';

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = []; // 返回对象（数据）

header('Content-type:text/html;charset=utf-8');

// 判断账号是否存在，若存在，调用发送接口
if (!empty($mobile)) {
    // 使用预处理语句查询用户
    $sql = "SELECT * FROM admin WHERE username = ? AND state = '1'";
    $stmt = $link->prepare($sql);
    
    if ($stmt === false) {
        $message = '数据库查询失败: ' . $link->error;
    } else {
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 1) {
            $send_res = sendRegisterCode($mobile); // 发送短信
            if ($send_res == "success") {
                $code = 200;
                $message = 'success';
            } else {
                $code = 202;
                $message = '发送失败，请联系管理员';
            }
        } else {
            $code = 202;
            $message = '请检查手机号是否有误！';
        }
        
        $stmt->close();
    }
}

/**
 * 发送短信验证码
 * @param string $mobile 要接收短信的手机号
 * @return string 发送结果
 */
function sendRegisterCode($mobile)
{
    // 生成随机验证码
    $num = substr(str_shuffle('1234567890'), -4);
    session_start();
    $_SESSION['admin_mobile_code'] = $num; // 将验证码存入session

    // 设置session的失效时间
    $session_id = session_id();
    setcookie("PHPSESSID", $session_id, time() + 300, "/", $_SERVER["HTTP_HOST"], false, true);

    // 使用短信发送接口
    $templateCode = 'SMS_89855365';  // 短信模板CODE
    $templateParamArray = ['code' => $num];  // 短信模板参数

    $response = sendSMS($mobile, $templateCode, $templateParamArray);

    return ($response['Code'] == 'OK') ? 'success' : 'error:' . $response['Message'];
}

// 关闭数据库链接
mysqli_close($link);

// 返回json
echo $jsonData->jsonData($code, $message, $obj);