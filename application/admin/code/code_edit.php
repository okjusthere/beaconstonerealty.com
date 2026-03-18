<?php
// 编辑嵌入代码信息

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
$state = isset($data->state) ? ($data->state ? 2 : 1) : 1; // 默认值为1
$code_msg = isset($data->code) ? htmlspecialchars($data->code) : '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("code")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 准备预处理语句
$sql = "UPDATE code SET state = ?, code = ? WHERE id = ?";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库更新准备失败: ' . $link->error;
} else {
    // 绑定参数
    $stmt->bind_param("isi", $state, $code_msg, $id);
    
    // 执行更新
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // 使用统一成功响应方法
            echo $jsonData->jsonSuccessData([]);
            $stmt->close();
            mysqli_close($link);
            exit;
        } else {
            $code = 404;
            $message = '未找到要更新的记录或数据未更改';
        }
    } else {
        $code = 100;
        $message = 'error';
    }
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);