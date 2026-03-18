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

$data = file_get_contents('php://input'); // 获取非表单数据
$data = json_decode($data); // 解码axios传递过来的json数据

$id = (int)$data->id; // 属性分类ID
$sort = (int)$data->sort; // 分类排序
$parentid = empty($data->parentid) ? 0 : (int)$data->parentid; // 父级ID
$title = $data->title; // 分类名称（不再使用addslashes）
$is_show = $data->is_show ? 2 : 1; // 是否显示
$screen_type = $data->screen_type; // 筛选方式
$is_top = $data->is_top ? 2 : 1; // 是否置顶

$attribute_add = $data->attribute_add; // 属性值相关信息
$attribute_delete = $data->attribute_delete; // 要删除的属性值id
$add_time = time(); // 添加时间

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 开始事务处理
$link->begin_transaction();

// 1. 更新主分类信息（使用预处理语句）
$sql = "UPDATE tb_product_attribute_class SET sort=?, parentid=?, title=?, is_show=?, screen_type=?, is_top=? WHERE id=?";
$stmt = $link->prepare($sql);
$success = $stmt && $stmt->bind_param('iissssi', $sort, $parentid, $title, $is_show, $screen_type, $is_top, $id) && $stmt->execute();

if ($success) {
    $stmt->close();
    
    // 2. 处理新增属性值
    if (!empty($attribute_add)) {
        $sql_attribute_value = "INSERT INTO tb_product_attribute_value (attribute_class, value, add_time, sort) VALUES (?, ?, ?, ?)";
        $stmt_value = $link->prepare($sql_attribute_value);
        
        foreach ($attribute_add as $value) {
            $value = (array)$value;
            $bind_success = $stmt_value && $stmt_value->bind_param('issi', $id, $value['value'], $add_time, $value['sort']) && $stmt_value->execute();
            
            if (!$bind_success) {
                $success = false;
                $message = '添加属性值时出错';
                break;
            }
        }
        
        if ($stmt_value) $stmt_value->close();
    }

    // 3. 处理删除属性值
    if ($success && !empty($attribute_delete)) {
        $placeholders = implode(',', array_fill(0, count($attribute_delete), '?'));
        $sql_del = "DELETE FROM tb_product_attribute_value WHERE id IN ($placeholders)";
        $stmt_del = $link->prepare($sql_del);
        
        // 动态绑定参数
        $types = str_repeat('i', count($attribute_delete));
        $bind_success = $stmt_del && $stmt_del->bind_param($types, ...$attribute_delete) && $stmt_del->execute();
        
        if (!$bind_success) {
            $success = false;
            $message = '删除属性值时出错';
        }
        
        if ($stmt_del) $stmt_del->close();
    }
    
    // 所有操作成功则提交事务
    if ($success) {
        $link->commit();
        $code = 200;
        $message = 'success';
    } else {
        $link->rollback();
        $code = 100;
    }
} else {
    // 更新主分类失败
    $link->rollback();
    $code = 100;
    $message = '更新分类信息失败';
    if ($stmt) $stmt->close();
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);