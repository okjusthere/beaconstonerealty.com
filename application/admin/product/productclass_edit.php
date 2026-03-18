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

// 初始化变量并验证输入
$before_data = isset($data->before_data) ? (array)($data->before_data) : '';
$before_template_id = (int)($before_data['template_id']); //之前的列表模板ID
$before_detail_template_id = (int)($before_data['detail_template_id']); //之前的详情模板ID
$before_static_url = $before_data['static_url']; //之前的伪静态链接
$before_is_custom = $before_data['is_custom'] ? '2' : '1'; //之前的是否自定义伪静态的状态

$id = $data->id ?? 0;
$rule_id = $data->rule_id ?? 0;
$paixu = $data->paixu ?? 0;
$parentid = isset($data->parentid) ? (int)$data->parentid : 0;
$template_id = isset($data->template_id) ? (int)$data->template_id : 0; //列表模板ID
$detail_template_id = isset($data->detail_template_id) ? (int)$data->detail_template_id : 0; //详情模板ID
$static_url = $data->static_url ?? ''; //伪静态链接
$is_custom = isset($data->is_custom) && $data->is_custom && !empty($static_url) ? '2' : '1'; //是否自定义伪静态{1：否；2：是}
$title = $data->title ?? ''; //分类标题
$description = $data->description ?? '';
$show_type = isset($data->show_type) ? (int)$data->show_type : 0;
$thumbnail = isset($data->thumbnail) && !empty($data->thumbnail) ?
    json_encode($data->thumbnail, JSON_UNESCAPED_UNICODE) : '';
$banner = isset($data->banner) && !empty($data->banner) ?
    json_encode($data->banner, JSON_UNESCAPED_UNICODE) : '';
$content = $data->content ?? '';
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$seo_title = $data->seo_title ?? '';
$seo_keywords = $data->seo_keywords ?? '';
$seo_description = $data->seo_description ?? '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("proclass_edit")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit; //终止继续执行
}

// 验证ID有效性、验证必要字段
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
    $list_page = $static_url;

    //当伪静态自定义状态从自定义改成系统生成时，或者原本就是系统生成但是修改了列表模板，获取相关模板名称，生成新的路由
    if ($is_custom === '1' && (($is_custom !== $before_is_custom) || ($template_id !== $before_template_id))) {
        $list_page = getRoutePage($template_id) . "/{$id}";
        //判断伪静态链接是否唯一
        if (haveSameStaticUrl($list_page)) {
            throw new Exception('伪静态 {$rule_page} 已存在，换个试试吧');
        }
    }

    if (empty($list_page)) {
        throw new Exception('无法获取有效的路由名称');
    }

    // 开启事务
    mysqli_autocommit($link, false);

    // 1. 只有路由相关信息发生变化时，才更新当前产品分类的路由规则
    if ($before_template_id !== $template_id || $before_is_custom !== $is_custom || $before_static_url !== $list_page) {
        $updateData = ["static_url" => "{$list_page}", "template_id" => $template_id, "is_custom" => $is_custom];
        if (!updateRecord('tb_rewrite_rules', [$rule_id], ["id"], $updateData)) {
            throw new Exception('路由信息更新失败');
        }
    }

    //2.如果更换了详情页模板，更新与该分类相关的所有产品详情的路由信息，自定义路由的不影响
    if ($detail_template_id !== $before_detail_template_id) {
        $detail_page = getRoutePage($detail_template_id); //获取新模板名称

        //获取该分类下所有的文章伪静态ID
        $sql_detail = "SELECT p.id,p.rule_id FROM product p INNER JOIN tb_rewrite_rules trr ON p.rule_id=trr.id WHERE trr.is_custom='1' and p.classid LIKE ?";
        $stmt_detail = $link->prepare($sql_detail);
        if (!$stmt_detail) {
            throw new Exception('查询该分类下的产品规则ID，数据库操作准备失败: ' . $link->error);
        }
        $classid_where = "%\"{$id}\"%"; //处理产品分类ID
        $stmt_detail->bind_param('s', $classid_where);
        $stmt_detail->execute();
        $result_detail = $stmt_detail->get_result();
        $detail_id_ary = []; //产品ID组成的数组
        $detail_rule_id_ary = []; //产品伪静态ID组成的数组
        while ($row = $result_detail->fetch_assoc()) {
            $detail_id_ary[] = $row["id"];
            $detail_rule_id_ary[] = $row["rule_id"];
        }

        //更新相关伪静态链接和模板ID
        if (!empty($detail_id_ary) && !empty($detail_rule_id_ary)) {
            $set_static_url = "static_url=CASE";
            $set_template_id = "template_id=CASE";

            foreach ($detail_rule_id_ary as $key => $item) {
                $detail_id = $detail_id_ary[$key];
                $static_url_item = "{$detail_page}/{$detail_id}";
                $set_static_url .= " WHEN id = {$item} THEN '{$static_url_item}'";
                $set_template_id .= " WHEN id = {$item} THEN {$detail_template_id}";
            }

            $set_static_url .= " ELSE static_url END";
            $set_template_id .= " ELSE template_id END";

            $in_rule_id = implode(',', $detail_rule_id_ary); //要修改的路由ID集合
            $sql_rule = "UPDATE tb_rewrite_rules SET {$set_static_url},{$set_template_id} where id IN ({$in_rule_id})";

            $stmt_rule = $link->prepare($sql_rule);
            if (!$stmt_rule) {
                throw new Exception('更新路由信息，数据库操作准备失败: ' . $link->error);
            }
            // 执行更新
            $result_rule = $stmt_rule->execute();
            if (!$result_rule) {
                throw new Exception('更新路由信息失败: ' . $link->error);
            }
        }
    }

    // 3. 修改产品分类
    $sql = "UPDATE product_class SET 
        parentid = ?, 
        detail_template_id = ?,
        title = ?, 
        description = ?, 
        show_type = ?, 
        thumbnail = ?, 
        banner = ?, 
        content = ?, 
        is_show = ?, 
        paixu = ?, 
        seo_title = ?, 
        seo_keywords = ?, 
        seo_description = ? 
        WHERE id = ?";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        throw new Exception('更新分类信息，数据库操作准备失败: ' . $link->error);
    }

    // 绑定参数
    $stmt->bind_param('iississssisssi',
        $parentid, $detail_template_id, $title, $description, $show_type, $thumbnail, $banner, $content, $is_show, $paixu, $seo_title, $seo_keywords, $seo_description, $id);

    // 执行更新
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('更新操作执行失败: ' . $link->error);
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
    if (isset($stmt_detail) && $stmt_detail instanceof mysqli_stmt) {
        $stmt_detail->close();
    }
    if (isset($stmt_rule) && $stmt_rule instanceof mysqli_stmt) {
        $stmt_rule->close();
    }
}

mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);