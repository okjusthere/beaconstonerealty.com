<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 准备数据
$sort = $data->sort;
$parentid = empty($data->parentid) ? 0 : $data->parentid;
$title = $data->title; // 移除了addslashes，预处理不需要
$add_time = time();
$is_show = $data->is_show ? 2 : 1;
$screen_type = $data->screen_type;
$is_top = $data->is_top ? 2 : 1;
$attribute = $data->attribute;

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 插入主分类（使用预处理）
$sql = "INSERT INTO tb_product_attribute_class (parentid, title, add_time, is_show, screen_type, is_top, sort) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($sql);

if ($stmt) {
    $stmt->bind_param('isssssi', $parentid, $title, $add_time, $is_show, $screen_type, $is_top, $sort);
    
    if ($stmt->execute()) {
        $record_id = $stmt->insert_id;
        $code = 200;
        $message = 'success';
        // updatelogs("产品属性，添加产品属性，ID：" . $record_id);

        // 处理属性值（使用预处理）
        if (count($attribute) > 0) {
            $attr_sql = "INSERT INTO tb_product_attribute_value (attribute_class, value, add_time, sort) VALUES (?, ?, ?, ?)";
            $attr_stmt = $link->prepare($attr_sql);
            
            if ($attr_stmt) {
                foreach ($attribute as $value) {
                    $value = (array)$value;
                    $attr_stmt->bind_param('issi', 
                        $record_id,
                        $value['value'],
                        $add_time,
                        $value['sort']
                    );
                    
                    if (!$attr_stmt->execute()) {
                        $code = 100;
                        $message = '属性值保存失败，请联系管理员！';
                        break;
                    }
                }
                $attr_stmt->close();
            } else {
                $code = 100;
                $message = '属性值预处理失败，请联系管理员！';
            }
        }
    } else {
        $code = 100;
        $message = '分类保存失败！';
    }
    $stmt->close();
} else {
    $code = 100;
    $message = '数据库预处理失败！';
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);