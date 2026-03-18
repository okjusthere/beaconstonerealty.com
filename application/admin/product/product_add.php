<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并解用json_decode码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}

// 初始化变量
$paixu = $data->paixu ?? 0; //排序
$classid = (isset($data->classid) && count($data->classid) > 0) ? json_encode($data->classid) : null; //所属分类ID
$template_id = $data->template_id ?? 0; //文章详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = (isset($data->is_custom) && $data->is_custom && !empty($static_url)) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = $data->title ?? ''; //产品名称
$specifications = $data->specifications ?? ''; //型号
$origin = $data->origin ?? ''; //产地
$price = $data->price ?? 0.00; //价格
$keywords = $data->keywords ?? ''; //关键词
$is_top = (isset($data->is_top) && $data->is_top) ? 2 : 1; //是否置顶
$is_show = isset($data->is_show) ? (int)$data->is_show : 2; //是否显示
$description = $data->description ?? ''; //描述
$thumbnail = count($data->thumbnail) == 0 ? '' : json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE);
$enclosure = count($data->enclosure) == 0 ? '' : json_encode($data->enclosure, JSON_UNESCAPED_UNICODE);
$photo_album = count($data->photo_album) == 0 ? '' : json_encode($data->photo_album, JSON_UNESCAPED_UNICODE);
$content = $data->content ?? ''; //详情
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

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("product_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit; //终止继续执行
}

//当开启自定义伪静态时，判断伪静态链接是否唯一
if ($is_custom === '2' && !empty($static_url) && haveSameStaticUrl($static_url)) {
    echo $jsonData->jsonData(400, "伪静态 {$static_url} 已存在，换个试试吧", []);
    exit;
}

// 验证必要字段
if (empty($classid) || empty($title) || empty($template_id)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再保存', []);
    exit;
}

try {
    // 获取路由名称
    $route_page = ($is_custom === '2' && !empty($static_url)) ? $static_url : getRoutePage($template_id);

    if (empty($route_page)) {
        throw new Exception('无法获取有效的路由名称');
    }

    // 开启事务
    mysqli_autocommit($link, false);

    // 1. 插入路由规则-获取对应记录的ID-这里随便拼凑一个路由，用来保证static_url唯一性好插入路由表
    $template_url = $is_custom === '2' ? $route_page : $route_page . time();
    $rule_id = insertRewriteRules($template_url, $template_id, $is_custom);
    if ($rule_id === 0) {
        throw new Exception('路由信息添加失败，请联系管理员！');
    }

    // 2.插入产品主表（使用预处理）
    $sql = "INSERT INTO product (classid, title, specifications, origin, price, keywords, description, thumbnail, enclosure, photo_album, content, add_time, is_show, is_top, paixu, rule_id, seo_title, seo_keywords, seo_description) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        throw new Exception('添加产品，数据库操作准备失败: ' . $link->error);
    }
    //绑定参数
    $stmt->bind_param('ssssdsssssssssiisss',
        $classid, $title, $specifications, $origin, $price, $keywords, $description,
        $thumbnail, $enclosure, $photo_album, $content, $add_time, $is_show, $is_top,
        $paixu, $rule_id, $seo_title, $seo_keywords, $seo_description
    );

    if (!$stmt->execute()) {
        throw new Exception('产品信息添加失败: ' . $stmt->error);
    }

    $r_id = $stmt->insert_id; //当前添加的产品ID

    // 处理字段信息（使用预处理）
    if (count($field) > 0) {
        $sql_field = "INSERT INTO field_info (table_name, record_id, field_name, field_content, add_time) VALUES (?, ?, ?, ?, ?)";
        $stmt_field = $link->prepare($sql_field);

        if (!$stmt_field) {
            throw new Exception('自定义字段语句准备失败: ' . $link->error);
        }

        foreach ($field as $value) {
            $value = object_array($value);
            if (in_array($value["field_type"], [3, 5, 6, 7])) {
                $value["field_content"] = json_encode($value["field_content"], JSON_UNESCAPED_UNICODE);
            }

            $stmt_field->bind_param('sisss',
                $value["table_name"],
                $r_id,
                $value["field_name"],
                $value["field_content"],
                $add_time
            );

            if (!$stmt_field->execute()) {
                throw new Exception('自定义字段添加失败: ' . $link->error);
            }
        }
    }

    // 处理产品属性（使用预处理）
    if (count($attribute) > 0) {
        $sql_attr = "INSERT INTO tb_product_attribute (product_id, attribute_value_id, attribute_value, price, inventory, photo_album, sort, add_time) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_attr = $link->prepare($sql_attr);

        if (!$stmt_attr) {
            throw new Exception('产品属性语句准备失败: ' . $link->error);
        }

        foreach ($attribute as $value) {
            $value = object_array($value);
            $attribute_value_id = count($value["attribute_value_id"]) == 0 ? '' : json_encode($value["attribute_value_id"]);
            $attribute_value = count($value["attribute_value"]) == 0 ? '' : json_encode($value["attribute_value"], JSON_UNESCAPED_UNICODE);
            $attr_photo_album = count($value["photo_album"]) == 0 ? '' : json_encode($value["photo_album"], JSON_UNESCAPED_UNICODE);

            $stmt_attr->bind_param('issdisis',
                $r_id,
                $attribute_value_id,
                $attribute_value,
                $value["price"],
                $value["inventory"],
                $attr_photo_album,
                $value["sort"],
                $add_time
            );

            if (!$stmt_attr->execute()) {
                throw new Exception('产品属性保存失败：' . $link->error);
            }
        }
    }

    // 3. 更新路由信息
    $updateData = $is_custom === '2'
        ? ["params" => json_encode(["id" => $r_id])]
        : ["static_url" => "{$route_page}/{$r_id}", "params" => json_encode(["id" => $r_id])];

    if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
        throw new Exception('路由信息更新失败');
    }

    updatelogs("产品管理，添加产品，ID：" . $r_id);

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';
    $obj = [];

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
    if (isset($stmt_attr) && $stmt_attr instanceof mysqli_stmt) {
        $stmt_attr->close();
    }
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);