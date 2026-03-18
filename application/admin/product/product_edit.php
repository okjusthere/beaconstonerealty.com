<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; // 获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

header('Content-Type:application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并解用json_decode码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}

// 初始化变量
$before_data = isset($data->before_data) ? (array)($data->before_data) : '';
$before_template_id = (int)$before_data['template_id']; //之前的列表模板ID
$before_static_url = $before_data['static_url']; //之前的伪静态链接
$before_is_custom = $before_data['is_custom'] ? '2' : '1'; //之前的是否自定义伪静态的状态

$id = $data->id ?? 0;
$rule_id = $data->rule_id ?? 0;
$paixu = $data->paixu ?? 0;
$classid = (isset($data->classid) && is_array($data->classid) && count($data->classid) > 0) ? json_encode($data->classid) : '';
$template_id = isset($data->template_id) ? (int)$data->template_id : 0; //产品详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = (isset($data->is_custom) && $data->is_custom && !empty($static_url)) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = $data->title ?? '';
$specifications = $data->specifications ?? '';
$origin = $data->origin ?? '';
$price = isset($data->price) ? (float)$data->price : 0.00;
$keywords = $data->keywords ?? '';
$is_top = $data->is_top ? 2 : 1;
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$description = $data->description;
$thumbnail = empty($data->thumbnail) ? '' : json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE);
$enclosure = empty($data->enclosure) ? '' : json_encode($data->enclosure, JSON_UNESCAPED_UNICODE);
$photo_album = empty($data->photo_album) ? '' : json_encode($data->photo_album, JSON_UNESCAPED_UNICODE);
$content = $data->content ?? '';
$add_time = time(); //添加时间
if (!empty($data->add_time)) { //如果自定义了产品添加时间，按照自定义的存入，否则读取当前时间
    $add_time = strtotime($data->add_time);
}
$seo_title = $data->seo_title ?? '';
$seo_keywords = $data->seo_keywords ?? '';
$seo_description = $data->seo_description ?? '';
$field = $data->field ?? [];
$attribute = $data->attribute ?? [];

$code = 500;
$message = '未响应，请重试！';
$obj = array();

// 判断后台用户权限
if (!my_power("product_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 验证变量有效性、验证必要字段
if ($id <= 0 || empty($title) || empty($rule_id) || empty($template_id) || empty($static_url)) {
    echo $jsonData->jsonData(400, '缺少必要字段，修改失败', []);
    exit;
}

//当开启自定义伪静态时，判断伪静态链接是否唯一
if ($static_url !== $before_static_url && haveSameStaticUrl($static_url)) {
    echo $jsonData->jsonData(400, "伪静态 {$static_url} 已存在，换个试试吧", []);
    exit;
}

try {
    //获取页面名称
    $rule_page = $static_url;
    //当伪静态自定义状态从自定义改成系统生成时，或者原本就是系统生成但是修改了列表模板，获取相关模板名称，生成新的路由
    if ($is_custom === '1' && ($is_custom !== $before_is_custom)) {
        $rule_page = getRoutePage($template_id) . "/{$id}";
        //判断伪静态链接是否唯一
        if ($rule_page != $before_static_url && haveSameStaticUrl($rule_page)) {
            throw new Exception('伪静态 {$rule_page} 已存在，换个试试吧');
        }
    }

    if (empty($rule_page)) {
        throw new Exception('无法获取有效的路由名称');
    }

    // 开始事务
    mysqli_autocommit($link, false);

    // 1.只有路由相关信息发生变化时，才更新当前文章的路由规则
    if ($before_is_custom !== $is_custom || $before_static_url !== $rule_page) {
        $updateData = ["static_url" => "{$rule_page}", "template_id" => $template_id, "is_custom" => $is_custom];
        if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
            throw new Exception('路由信息更新失败');
        }
    }

    // 2.更新产品主表数据
    $sql = "UPDATE product SET classid=?, title=?, specifications=?, origin=?, price=?, keywords=?, 
        description=?, thumbnail=?, enclosure=?, photo_album=?, content=?, add_time=?, is_show=?, 
        is_top=?, paixu=?, seo_title=?, seo_keywords=?, seo_description=? WHERE id=?";
    $stmt = $link->prepare($sql);

    if (!$stmt) {
        throw new Exception('更新产品信息，数据库操作准备失败: ' . $link->error);
    }

    //绑定参数
    $stmt->bind_param('ssssdsssssssssisssi',
        $classid, $title, $specifications, $origin, $price, $keywords,
        $description, $thumbnail, $enclosure, $photo_album, $content, $add_time,
        $is_show, $is_top, $paixu, $seo_title, $seo_keywords, $seo_description, $id);

    // 执行更新
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('更新操作执行失败: ' . $link->error);
    }

    // 更新字段信息表
    if (!empty($field)) {
        $sql_field = "UPDATE field_info SET field_content = ? WHERE record_id = ? AND table_name = 'product' AND field_name = ?";
        // 准备更新语句
        $stmt_field = $link->prepare($sql_field);
        if (!$stmt_field) {
            throw new Exception('自定义字段更新准备失败');
        }

        foreach ($field as $value) {
            $value = object_array($value);
            $field_content = $value["field_content"];

            if (in_array($value["field_type"], [3, 5, 6, 7])) {
                $field_content = json_encode($field_content, JSON_UNESCAPED_UNICODE);
            }

            $stmt_field->bind_param("sis", $field_content, $id, $value["field_name"]);
            if (!$stmt_field->execute()) {
                throw new Exception('部分自定义字段更新失败');
            }
        }
    }

    // 处理产品属性
    if (!empty($attribute)) {
        $haveID = isset(object_array($attribute[0])["id"]);
        $add_time = time();

        if ($haveID) {
            // 更新现有属性
            foreach ($attribute as $value) {
                $value = object_array($value);
                $attribute_value_id = empty($value["attribute_value_id"]) ? '' : json_encode($value["attribute_value_id"]);
                $attribute_value = empty($value["attribute_value"]) ? '' : json_encode($value["attribute_value"], JSON_UNESCAPED_UNICODE);
                $attr_photo_album = empty($value["photo_album"]) ? '' : json_encode($value["photo_album"], JSON_UNESCAPED_UNICODE);

                if (!updateProductAttribute($link, $id, $attribute_value_id, $attribute_value,
                    $value["price"], $value["inventory"], $attr_photo_album,
                    $value["sort"], $value["id"])) {
                    throw new Exception('产品属性更新失败');
                }
            }
        } else {
            // 删除旧属性并添加新属性
            $stmt_del = $link->prepare("DELETE FROM tb_product_attribute WHERE product_id=?");
            //参数绑定
            $stmt_del->bind_param('i', $id);
            //执行语句
            $result_del = $stmt_del->execute();
            if (!$result_del) {
                throw new Exception('原产品属性删除失败');
            }

            // 批量插入新属性
            $sql_attr = "INSERT INTO tb_product_attribute (product_id, attribute_value_id, attribute_value, price, inventory, photo_album, sort, add_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_attr = $link->prepare($sql_attr);
            if (!$stmt_attr) {
                throw new Exception('自定义字段更新准备失败');
            }

            foreach ($attribute as $value) {
                $value = object_array($value);
                $attribute_value_id = empty($value["attribute_value_id"]) ? '' : json_encode($value["attribute_value_id"]);
                $attribute_value = empty($value["attribute_value"]) ? '' : json_encode($value["attribute_value"], JSON_UNESCAPED_UNICODE);
                $attr_photo_album = empty($value["photo_album"]) ? '' : json_encode($value["photo_album"], JSON_UNESCAPED_UNICODE);

                //绑定参数
                $stmt_attr->bind_param('issdisis', $id, $attribute_value_id, $attribute_value, $value["price"], $value["inventory"], $attr_photo_album,
                    $value["sort"], $add_time);
                //执行更新
                $result_attr = $stmt_attr->execute();
                if (!$result_attr) {
                    throw new Exception('产品属性插入失败');
                }
            }
        }
    }

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';

} catch (Exception $e) {
    // 回滚事务
    if (isset($link)) {
        mysqli_rollback($link);
        mysqli_autocommit($link, true);
    }

    $code = 100;
    $message = $e->getMessage();

} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($stmt_field) && $stmt_field instanceof mysqli_stmt) {
        $stmt_field->close();
    }
    if (isset($stmt_del) && $stmt_del instanceof mysqli_stmt) {
        $stmt_del->close();
    }
    if (isset($stmt_attr) && $stmt_attr instanceof mysqli_stmt) {
        $stmt_attr->close();
    }
}

//关闭数据库链接
mysqli_close($link);

// 辅助函数
function updateProductAttribute($link, $product_id, $attribute_value_id, $attribute_value, $price, $inventory, $photo_album, $sort, $id): bool
{
    $sql = "UPDATE tb_product_attribute SET product_id=?, attribute_value_id=?, attribute_value=?, 
            price=?, inventory=?, photo_album=?, sort=? WHERE id=?";
    $stmt = $link->prepare($sql);

    return $stmt && $stmt->bind_param('issdisii', $product_id, $attribute_value_id, $attribute_value,
            $price, $inventory, $photo_album, $sort, $id) && $stmt->execute();
}

echo $jsonData->jsonData($code, $message, $obj);