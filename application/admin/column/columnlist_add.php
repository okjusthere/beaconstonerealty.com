<?php
// 添加栏目表信息

// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

// 获取并验证输入数据
$input = file_get_contents('php://input');
$data = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的JSON数据', []);
    exit;
}

// 提取并过滤数据
$paixu = (int)$data->paixu;
$parentid = isset($data->parentid) ? (int)$data->parentid : 0;
$type = isset($data->type) ? (int)$data->type : 0;
$link_id = isset($data->link_id) ? (int)$data->link_id : 0;
$title = isset($data->title) ? trim($data->title) : '';
$sub_title = isset($data->sub_title) ? trim($data->sub_title) : '';
$url = isset($data->url) ? trim($data->url) : '';
$remarks = isset($data->remarks) ? trim($data->remarks) : '';
$thumbnail = (isset($data->thumbnail) && is_array($data->thumbnail) && !empty($data->thumbnail)) ? json_encode($data->thumbnail) : '';
$banner = (isset($data->banner) && is_array($data->banner) && !empty($data->banner)) ? json_encode($data->banner) : '';
$add_time = time();
$is_show = isset($data->is_show) ? (int)$data->is_show : 2;
$seo_title = isset($data->seo_title) ? trim($data->seo_title) : '';
$seo_keywords = isset($data->seo_keywords) ? trim($data->seo_keywords) : '';
$seo_description = isset($data->seo_description) ? trim($data->seo_description) : '';

$code = 500;
$message = '未响应，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("column_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

try {
    // 开启事务
    mysqli_autocommit($link, false);

    // 准备预处理语句
    $sql = "INSERT INTO column_list 
        (paixu, parentid, type, link_id, title, sub_title, url, remarks, 
         thumbnail, banner, add_time, is_show, seo_title, seo_keywords, seo_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) throw new Exception('数据库准备失败：' . $link->error);

    // 绑定参数
    $stmt->bind_param(
        "iiiisssssssssss",
        $paixu, $parentid, $type, $link_id,
        $title, $sub_title, $url, $remarks,
        $thumbnail, $banner, $add_time, $is_show,
        $seo_title, $seo_keywords, $seo_description
    );

    // 执行插入
    if (!$stmt->execute()) throw new Exception('导航菜单添加失败: ' . $link->error);

    $r_id = $stmt->insert_id; //获取插入的导航菜单ID

    updatelogs("添加导航菜单，ID：" . $r_id);

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
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);