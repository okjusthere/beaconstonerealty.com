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

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并解用json_decode码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}

// 初始化变量
$paixu = (int)$data->paixu;
$parentid = isset($data->parentid) ? (int)$data->parentid : 0;
$template_id = isset($data->template_id) ? (int)$data->template_id : 0; //列表模板ID
$detail_template_id = isset($data->detail_template_id) ? (int)$data->detail_template_id : 0; //详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = (isset($data->is_custom) && $data->is_custom && !empty($static_url)) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = $data->title ?? '';
$description = $data->description ?? '';
$show_type = isset($data->show_type) ? (int)$data->show_type : 0;
$thumbnail = isset($data->thumbnail) && !empty($data->thumbnail) ?
    json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';
$banner = isset($data->banner) && !empty($data->banner) ?
    json_encode($data->banner, JSON_UNESCAPED_UNICODE) : '';
$content = $data->content ?? '';
$add_time = time();
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$seo_title = $data->seo_title ?? '';
$seo_keywords = $data->seo_keywords ?? '';
$seo_description = $data->seo_description ?? '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("proclass_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', $obj);
    exit;
}

// 验证必要字段
if (empty($title)) {
    echo $jsonData->jsonData(400, '分类名称不能为空', []);
    exit;
}

//当开启自定义伪静态时，判断伪静态链接是否唯一
if ($is_custom === '2' && !empty($static_url) && haveSameStaticUrl($static_url)) {
    echo $jsonData->jsonData(400, "伪静态 {$static_url} 已存在，换个试试吧", []);
    exit;
}

try {
    // 获取列表页名称
    $list_page = ($is_custom === '2' && !empty($static_url)) ? $static_url : getRoutePage($template_id);

    if (empty($list_page)) {
        throw new Exception('无法获取有效的路由名称');
    }

    // 开启事务
    mysqli_autocommit($link, false);

    $list_page_temp = $is_custom === '2' ? $list_page : "temp_{$list_page}" . time();
    // 1. 插入路由规则
    $rule_id = insertRewriteRules($list_page_temp, $template_id, $is_custom);
    if ($rule_id === 0) {
        throw new Exception('路由信息添加失败，请联系管理员！');
    }

    // 2. 插入产品分类
    $sql = "INSERT INTO product_class 
        (parentid, title, description, show_type, thumbnail, banner, content, 
         add_time, is_show, paixu,detail_template_id, rule_id, seo_title, seo_keywords, seo_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        throw new Exception('数据库操作准备失败: ' . $link->error);
    }

    // 绑定参数
    $stmt->bind_param('ississsssiiisss',
        $parentid, $title, $description, $show_type, $thumbnail, $banner,
        $content, $add_time, $is_show, $paixu, $detail_template_id, $rule_id, $seo_title, $seo_keywords, $seo_description);

    if (!$stmt->execute()) {
        throw new Exception('分类信息插入失败: ' . $stmt->error);
    }

    $r_id = $stmt->insert_id; //当前添加的产品分类ID

    // 3. 更新路由信息
    $updateData = $is_custom === '2'
        ? ["params" => json_encode(["id" => $r_id])]
        : ["static_url" => "{$list_page}/{$r_id}", "params" => json_encode(["id" => $r_id])];

    if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
        throw new Exception('路由信息更新失败');
    }

    // 记录操作日志
    updatelogs("产品管理，添加产品分类，ID：" . $r_id);

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
}

mysqli_close($link);

// 返回错误响应
echo $jsonData->jsonData($code, $message, $obj);
