<?php
// 按钮 删除文章分类信息
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

$code = 500;  // 响应码
$message = '删除失败，请重试！';  // 响应信息
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("linksclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    die();
}

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);

// 验证JSON数据
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    die($jsonData->jsonData(400, '无效的JSON数据', []));
}

// 验证ID参数
if (!isset($data->id) || !is_numeric($data->id)) {
    die($jsonData->jsonData(400, '无效的ID参数', []));
}

$id = (int)$data->id;

// 使用预处理语句执行删除
$sql = 'DELETE FROM tb_links_class WHERE id = ?';
$stmt = $link->prepare($sql);

if ($stmt === false) {
    $message = '数据库预处理失败: ' . $link->error;
} else {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $code = 200;
            $message = 'success';
            updatelogs("友情链接，删除友链分类，ID：" . $id); // 记录操作日志
        } else {
            $code = 404;
            $message = '未找到要删除的记录';
        }
    } else {
        $message = '删除操作失败: ' . $stmt->error;
    }
    
    $stmt->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);