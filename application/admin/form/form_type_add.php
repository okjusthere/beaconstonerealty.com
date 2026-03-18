<?php
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
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->title, $data->field, $data->field_show, $data->state)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少必要参数', []);
    exit;
}

// 处理输入数据
$title = $data->title;
$field = !empty($data->field) ? json_encode($data->field, JSON_UNESCAPED_UNICODE) : '';
$field_show = !empty($data->field_show) ? json_encode($data->field_show, JSON_UNESCAPED_UNICODE) : '';
$state = $data->state ? 1 : 2;

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_type_add")) {
    echo $jsonData->jsonData(403, '暂无权限，请联系管理员！', []);
    exit;
}

// 使用预处理语句插入数据
$sql = "INSERT INTO tb_form_type (title, field, field_show, state) VALUES (?, ?, ?, ?)";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败: ' . $link->error, []);
    exit;
}

$stmt->bind_param("sssi", $title, $field, $field_show, $state);
$result = $stmt->execute();

if ($result) {
    $r_id = $stmt->insert_id;
    $code = 200;
    $message = 'success';
    updatelogs("管理中心，添加表单，ID：" . $r_id);
} else {
    $code = 500;
    $message = '表单添加失败: ' . $stmt->error;
}

$stmt->close();
mysqli_close($link);

echo $jsonData->jsonData($code, $message, []);