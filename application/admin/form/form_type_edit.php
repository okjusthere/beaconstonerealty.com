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
if ($data === null || !isset($data->id, $data->title, $data->field, $data->state)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少必要参数', []);
    exit;
}

// 处理输入数据
$id = (int)$data->id;
$title = $data->title; // 移除了不安全的addslashes()
$field = !empty($data->field) ? json_encode($data->field, JSON_UNESCAPED_UNICODE) : '';
$state = $data->state ? 1 : 2;

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_type_edit")) {
    echo $jsonData->jsonData(403, '暂无权限，请联系管理员！', []);
    exit;
}

// 验证ID有效性
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的表单分类ID', []);
    exit;
}

// 使用预处理语句更新数据
$sql = "UPDATE tb_form_type SET title = ?, field = ?, state = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    echo $jsonData->jsonData(500, '数据库预处理失败', []);
    exit;
}

$stmt->bind_param("ssii", $title, $field, $state, $id);
$result = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($result) {
    if ($affected_rows > 0) {
        $code = 200;
        $message = 'success';
        updatelogs("表单分类更新，ID：" . $id); // 记录操作日志
    } else {
        $code = 200;
        $message = 'success';
    }
} else {
    $code = 500;
    $message = 'error';
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, []);