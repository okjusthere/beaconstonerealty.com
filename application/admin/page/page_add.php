<?php
//新增文章--页面
include_once '../checking_user.php';
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
$template_id = $data->template_id ?? 0; //文章详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = (isset($data->is_custom) && $data->is_custom && !empty($static_url)) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = isset($data->title) ? $data->title : '';
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$description = isset($data->description) ? $data->description : '';
$thumbnail = isset($data->thumbnail) && count($data->thumbnail) > 0 ? json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';
$photo_album = isset($data->photo_album) && count($data->photo_album) > 0 ? json_encode($data->photo_album, JSON_UNESCAPED_UNICODE) : '';
$content = isset($data->content) ? $data->content : '';
$add_time = time();
$seo_title = isset($data->seo_title) ? $data->seo_title : '';
$seo_keywords = isset($data->seo_keywords) ? $data->seo_keywords : '';
$seo_description = isset($data->seo_description) ? $data->seo_description : '';
$field = isset($data->field) ? $data->field : [];

$code = 500;
$message = '未响应，请重试！';
$obj = array();

// 验证必要字段
if (empty($title) || empty($template_id)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再保存');
    exit;
}

// 权限检查
if (!my_power("page_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit; //终止继续执行
}

//当开启自定义伪静态时，判断伪静态链接是否唯一
if ($is_custom === '2' && !empty($static_url) && haveSameStaticUrl($static_url)) {
    echo $jsonData->jsonData(400, "伪静态 {$static_url} 已存在，换个试试吧");
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

    // 1. 插入路由规则-获取对应记录的ID
    $template_url = $is_custom === '2' ? $route_page : $route_page . time();
    $rule_id = insertRewriteRules($template_url, $template_id, $is_custom);
    if ($rule_id === 0) throw new Exception('路由信息添加失败，请联系管理员！');

    // 2. 插入主表数据
    $sql = "INSERT INTO news (title, description, thumbnail, photo_album, content, add_time, rule_id, is_show, seo_title, seo_keywords, seo_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('添加页面，数据库操作准备失败: ' . $link->error);

    // 绑定参数
    $stmt->bind_param("ssssssissss",
        $title, $description, $thumbnail, $photo_album,
        $content, $add_time, $rule_id, $is_show,
        $seo_title, $seo_keywords, $seo_description
    );

    // 执行插入
    if (!$stmt->execute()) throw new Exception('页面添加失败: ' . $stmt->error);

    $r_id = $stmt->insert_id; //当前添加的页面ID

    // 3. 处理自定义字段
    if (count($field) > 0) {
        $stmt_field = $link->prepare("INSERT INTO field_info (table_name, record_id, field_name, field_content, add_time) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt_field) throw new Exception('自定义字段语句准备失败: ' . $link->error);

        foreach ($field as $value) {
            $value = object_array($value);
            $field_content = $value["field_content"];

            if (in_array($value["field_type"], [3, 5, 6, 7])) {
                $field_content = json_encode($field_content, JSON_UNESCAPED_UNICODE);
            }

            $stmt_field->bind_param("sisss",
                $value["table_name"],
                $r_id,
                $value["field_name"],
                $field_content,
                $add_time
            );

            if (!$stmt_field->execute()) throw new Exception('自定义字段添加失败: ' . $link->error);
        }
    }

    // 4. 更新路由信息
    $updateData = $is_custom === '2'
        ? ["params" => json_encode(["id" => $r_id, "use_from"=>"page"])]
        : ["static_url" => "{$route_page}/{$r_id}", "params" => json_encode(["id" => $r_id, "use_from"=>"page"])];

    if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
        throw new Exception('路由信息更新失败');
    }

    // 记录操作日志
    updatelogs("页面管理，添加页面，ID：" . $r_id);

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

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);