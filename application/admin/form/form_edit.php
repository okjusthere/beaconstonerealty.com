<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$basic = new \basic\Basic();

// 获取并验证输入数据
$input = file_get_contents('php://input');
if ($input === false) {
    echo $jsonData->jsonData(400, '无法获取请求数据', []);
    exit;
}

$data = json_decode($input);
if ($data === null || !isset($data->id, $data->state, $data->remarks)) {
    echo $jsonData->jsonData(400, '无效的请求数据或缺少必要参数', []);
    exit;
}

// 验证和处理参数
$id = (int)$data->id;
$state = $data->state;
$remarks = $basic::filterStr($data->remarks); // 使用Basic类过滤字符串

// 验证状态值
if (!in_array($state, ['read', 'unread'])) { // 根据实际状态值调整
    echo $jsonData->jsonData(400, '无效的状态值', []);
    exit;
}

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("form_type_edit")) {
    echo $jsonData->jsonData(403, '暂无权限，请联系管理员！', []);
    exit;
}

if ($id > 0) {
    // 使用预处理语句更新数据
    $sql = "UPDATE tb_form SET state = ?, remarks = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo $jsonData->jsonData(500, '数据库预处理失败', []);
        exit;
    }
    
    $stmt->bind_param("ssi", $state, $remarks, $id);
    $result = $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($result && $affected_rows >= 0) {
        $code = 200;
        $message = 'success';
    } else {
        $code = 500;
        $message = 'error';
    }
} else {
    echo $jsonData->jsonData(400, '无效的表单ID', []);
    exit;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, []);