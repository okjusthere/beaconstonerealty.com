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

$code = 500;
$message = '删除失败，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("proclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit;
}

$data = file_get_contents('php://input');
$data = json_decode($data);

// 验证输入数据
if (!isset($data->id)) {
    echo $jsonData->jsonData(400, '缺少必要参数', $obj);
    exit;
}

$id = (int)$data->id;
if ($id <= 0) {
    echo $jsonData->jsonData(400, '无效的分类ID', $obj);
    exit;
}

// 检查是否有子分类
$sql_check = "SELECT id FROM product_class WHERE parentid = ? LIMIT 1";
$stmt_check = $link->prepare($sql_check);

if ($stmt_check) {
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $code = 100;
        $message = '请先删除该分类下的子分类';
        $stmt_check->close();
    } else {
        $stmt_check->close();
        
        // 执行逻辑删除（放入回收站）
        $sql_delete = "UPDATE product_class SET is_delete = 1 WHERE id = ?";
        $stmt_delete = $link->prepare($sql_delete);
        
        if ($stmt_delete) {
            $stmt_delete->bind_param('i', $id);
            
            if ($stmt_delete->execute()) {
                $affected_rows = $stmt_delete->affected_rows;
                
                if ($affected_rows > 0) {
                    $code = 200;
                    $message = 'success';
                    updatelogs("产品管理，产品分类放入回收站，ID：" . $id);
                } else {
                    $code = 404;
                    $message = '未找到要删除的分类';
                }
            } else {
                $code = 100;
                $message = '删除操作执行失败: ' . $stmt_delete->error;
            }
            
            $stmt_delete->close();
        } else {
            $code = 100;
            $message = 'SQL预处理失败: ' . $link->error;
        }
    }
} else {
    $code = 100;
    $message = 'SQL预处理失败: ' . $link->error;
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);