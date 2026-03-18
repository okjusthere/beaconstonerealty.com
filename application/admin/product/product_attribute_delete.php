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

$code = 500;  // 响应码
$message = '删除失败，请重试！';  // 响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

$data = file_get_contents('php://input'); // 获取非表单数据
$data = json_decode($data); // 解码axios传递过来的json数据
$id = (int)$data->id;

// 开始事务处理
$link->begin_transaction();

// 1. 检查是否有子分类（使用预处理语句）
$sql_have = 'SELECT id FROM tb_product_attribute_class WHERE parentid = ?';
$stmt_have = $link->prepare($sql_have);
$stmt_have->bind_param('i', $id);
$stmt_have->execute();
$result_have = $stmt_have->get_result();

if ($result_have->num_rows > 0) {
    $code = 100;
    $message = '请先删除该分类下的子分类';
    $stmt_have->close();
} else {
    $stmt_have->close();
    
    // 2. 先删除属性值（使用预处理语句）
    $sql_value = "DELETE FROM tb_product_attribute_value WHERE attribute_class = ?";
    $stmt_value = $link->prepare($sql_value);
    $stmt_value->bind_param('i', $id);
    $value_result = $stmt_value->execute();
    
    if ($value_result) {
        // 3. 再删除分类（使用预处理语句）
        $sql = "DELETE FROM tb_product_attribute_class WHERE id = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param('i', $id);
        $res = $stmt->execute();
        
        if ($res) {
            $link->commit();  // 提交事务
            $code = 200;
            $message = 'success';
        } else {
            $link->rollback();  // 回滚事务
            $code = 100;
            $message = '删除失败，请重试，或联系管理员！';
        }
        $stmt->close();
    } else {
        $link->rollback();  // 回滚事务
        $code = 100;
        $message = '属性分类对应的属性值删除失败，请联系管理员！';
    }
    $stmt_value->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);