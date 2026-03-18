<?php
//编辑文章
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
$template_id = isset($data->template_id) ? (int)$data->template_id : 0; //文章详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = (isset($data->is_custom) && $data->is_custom && !empty($static_url)) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = $data->title ?? '';
$url = $data->url ?? '';
$keywords = $data->keywords ?? '';
$is_top = (isset($data->is_top) && $data->is_top) ? 2 : 1;
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$description = $data->description ?? '';
$thumbnail = (isset($data->thumbnail) && count($data->thumbnail) > 0) ? json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';
$enclosure = (isset($data->enclosure) && count($data->enclosure)) > 0 ? json_encode($data->enclosure, JSON_UNESCAPED_UNICODE) : '';
$photo_album = (isset($data->photo_album) && count($data->photo_album)) > 0 ? json_encode($data->photo_album, JSON_UNESCAPED_UNICODE) : '';
$content = $data->content ?? '';
$add_time = time();
if (isset($data->add_time) && !empty($data->add_time)) {
    $add_time = strtotime($data->add_time);
}
$view = $data->view ?? 0;
$seo_title = isset($data->seo_title) ? $data->seo_title : '';
$seo_keywords = isset($data->seo_keywords) ? $data->seo_keywords : '';
$seo_description = isset($data->seo_description) ? $data->seo_description : '';
$field = $data->field ?? [];

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

// 验证变量有效性、验证必要字段
if ($id <= 0 || empty($title) || empty($rule_id) || empty($template_id) || empty($static_url)) {
    echo $jsonData->jsonData(400, '缺少必要字段，修改失败', []);
    exit;
}

//判断后台用户权限
if (!my_power("news_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit; //终止继续执行
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

    // 开启事务
    mysqli_autocommit($link, false);

    // 1.只有路由相关信息发生变化时，才更新当前文章的路由规则
    if ($before_is_custom !== $is_custom || $before_static_url !== $rule_page) {
        $updateData = ["static_url" => "{$rule_page}", "template_id" => $template_id, "is_custom" => $is_custom];
        if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
            throw new Exception('路由信息更新失败');
        }
    }

    // 2.更新文章主表数据
    $sql = "UPDATE news SET classid = ?, title = ?, url = ?, keywords = ?, description = ?,thumbnail = ?, enclosure = ?, photo_album = ?, content = ?, is_show = ?, is_top = ?, paixu = ?, view = ?, add_time = ?, seo_title = ?, 
     seo_keywords = ?, seo_description = ? WHERE id = ?";
    $stmt = $link->prepare($sql);

    if (!$stmt) throw new Exception('更新文章信息，数据库操作准备失败: ' . $link->error);

    // 绑定参数
    $stmt->bind_param("sssssssssssiissssi",
        $classid, $title, $url, $keywords, $description,
        $thumbnail, $enclosure, $photo_album, $content,
        $is_show, $is_top, $paixu, $view, $add_time,
        $seo_title, $seo_keywords, $seo_description,
        $id
    );

    // 执行更新
    $result = $stmt->execute();
    if (!$result) throw new Exception('更新操作执行失败: ' . $link->error);

    // 更新自定义字段
    if (!empty($field)) {
        $sql_field = "UPDATE field_info SET field_content = ? WHERE record_id = ? AND table_name = 'news' AND field_name = ?";
        // 准备更新语句
        $stmt_field = $link->prepare($sql_field);
        if (!$stmt_field) throw new Exception('自定义字段更新准备失败');

        foreach ($field as $value) {
            $value = object_array($value);
            $field_content = $value["field_content"];

            if (in_array($value["field_type"], [3, 5, 6, 7])) {
                $field_content = json_encode($field_content, JSON_UNESCAPED_UNICODE);
            }

            //绑定参数
            $stmt_field->bind_param("sis", $field_content, $id, $value["field_name"]);
            //执行更新
            $result_field = $stmt_field->execute();
            if (!$result_field) throw new Exception('部分自定义字段更新失败');
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
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);