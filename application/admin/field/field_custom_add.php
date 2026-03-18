<?php
// 添加文章信息
include_once '../checking_user.php';
include_once '../../../wf-config.php';
global $link;
include_once '../../../myclass/ResponseJson.php';

class myData {
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = file_get_contents('php://input');
$data = json_decode($data);

$sort = $data->sort ?? '';
$table_name = $data->table_name ?? '';
$field_name = $data->field_name ?? '';
$field_title = $data->field_title ?? '';
$field_type = $data->field_type ?? '';
$field_default_value = isset($data->field_default_value) && count($data->field_default_value) == 0 ? '' : json_encode($data->field_default_value, JSON_UNESCAPED_UNICODE);
$add_time = time();

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("field_add")) {
    $code = 201;
    $message = '您没有管理该页面的权限！';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 检查字段是否已存在
$sql_check = "SELECT COUNT(*) AS number FROM information_schema.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?";
$stmt_check = $link->prepare($sql_check);

if (!$stmt_check) {
    $code = 500;
    $message = '数据库查询准备失败: ' . $link->error;
} else {
    $stmt_check->bind_param("ss", $table_name, $field_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $info = $result_check->fetch_assoc();
    $stmt_check->close();

    if ($info["number"] == 0) {
        // 检查自定义字段表中是否已存在
        $sql_check_field = "SELECT COUNT(*) AS number FROM field_custom WHERE table_name = ? AND field_name = ?";
        $stmt_check_field = $link->prepare($sql_check_field);
        
        if (!$stmt_check_field) {
            $code = 500;
            $message = '数据库查询准备失败: ' . $link->error;
        } else {
            $stmt_check_field->bind_param("ss", $table_name, $field_name);
            $stmt_check_field->execute();
            $result_check_field = $stmt_check_field->get_result();
            $info_field = $result_check_field->fetch_assoc();
            $stmt_check_field->close();

            if ($info_field["number"] == 0) {
                // 插入字段信息
                $sql = "INSERT INTO field_custom (sort, table_name, field_name, field_title, field_type, field_default_value, add_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $link->prepare($sql);
                
                if (!$stmt) {
                    $code = 500;
                    $message = '数据库插入准备失败: ' . $link->error;
                } else {
                    $stmt->bind_param("isssssi", $sort, $table_name, $field_name, $field_title, $field_type, $field_default_value, $add_time);
                    
                    if ($stmt->execute()) {
                        $code = 200;
                        $message = 'success';
                        
                        // 获取表中所有ID
                        $sql_table = "SELECT id FROM " . $table_name; // 注意：表名不能参数化
                        $res_table = mysqli_query($link, $sql_table);
                        
                        if ($res_table) {
                            $id_ary = array();
                            while ($row = mysqli_fetch_assoc($res_table)) {
                                $id_ary[] = $row["id"];
                            }
                            
                            if (count($id_ary) > 0) {
                                // 准备批量插入语句
                                $sql_field_info = "INSERT INTO field_info(table_name, record_id, field_name, field_content, add_time) VALUES (?, ?, ?, ?, ?)";
                                $stmt_field_info = $link->prepare($sql_field_info);
                                
                                if (!$stmt_field_info) {
                                    $code = 500;
                                    $message = '数据库插入准备失败: ' . $link->error;
                                } else {
                                    $field_content = ($field_type == 3 || $field_type == 5 || $field_type == 6 || $field_type == 7) ? json_encode(array()) : '';
                                    
                                    foreach ($id_ary as $value) {
                                        $stmt_field_info->bind_param("sisss", $table_name, $value, $field_name, $field_content, $add_time);
                                        if (!$stmt_field_info->execute()) {
                                            $code = 100;
                                            $message = '出错了，请联系管理员！';
                                            break;
                                        }
                                    }
                                    $stmt_field_info->close();
                                }
                            }
                        }
                    } else {
                        $code = 100;
                        $message = '出错了，请联系管理员！';
                    }
                    $stmt->close();
                }
            } else {
                $code = 100;
                $message = '字段 ' . $field_name . ' 已存在，换个试试吧！';
            }
        }
    } else {
        $code = 100;
        $message = $field_name . ' 是一个保留字段名，换个试试吧！';
    }
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);