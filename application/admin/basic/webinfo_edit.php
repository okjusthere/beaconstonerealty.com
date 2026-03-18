<?php
// 编辑网站信息

// 查看是否有访问权限
include_once '../checking_user.php';

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
if (json_last_error() !== JSON_ERROR_NONE || !isset($data->id)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}

// 提取并过滤数据
$id = (int)$data->id;
$company = isset($data->company) ? trim($data->company) : '';
$address = isset($data->address) ? trim($data->address) : '';
$phone = isset($data->phone) ? trim($data->phone) : '';
$mobile = isset($data->mobile) ? trim($data->mobile) : '';
$email = isset($data->email) ? trim($data->email) : '';
$fax = isset($data->fax) ? trim($data->fax) : '';
$contact = isset($data->contact) ? trim($data->contact) : '';
$qq = isset($data->qq) ? trim($data->qq) : '';
$wechat = isset($data->wechat) ? trim($data->wechat) : '';
$zip = isset($data->zip) ? trim($data->zip) : '';
$icp = isset($data->icp) ? trim($data->icp) : '';
$icp_police = isset($data->icp_police) ? trim($data->icp_police) : '';
$weburl = isset($data->weburl) ? trim($data->weburl) : '';
$whatsapp = isset($data->whatsapp) ? trim($data->whatsapp) : '';
$map = isset($data->map) ? trim($data->map) : '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("webinfo")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 准备预处理语句
$sql = "UPDATE web_info SET company = ?, address = ?, phone = ?, mobile = ?, email = ?, fax = ?, contact = ?, qq = ?, wechat = ?, zip = ?, icp = ?, icp_police = ?, weburl = ?, whatsapp = ?, map = ? WHERE id = ?";

$stmt = $link->prepare($sql);
if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库更新准备失败: ' . $link->error, []);
    exit;
}

// 绑定参数
$stmt->bind_param("sssssssssssssssi", $company, $address, $phone, $mobile, $email, $fax, $contact, $qq, $wechat, $zip, $icp, $icp_police, $weburl, $whatsapp, $map, $id);

// 执行更新
if ($stmt->execute()) {
    $code = 200;
    $message = 'success';
    // updatelogs("基础设置，修改网站信息");
} else {
    $code = 100;
    $message = 'error';
}

// 关闭语句和连接
$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);