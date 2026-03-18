<?php
//获取文章表信息--页面
include_once '../checking_user.php';
include_once '../../../wf-config.php';
include_once '../../../myclass/ResponseJson.php';

global $link;

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 权限检查
if (!my_power("page_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit;
}

// 初始化变量
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1;
$pagesize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize']) : 10;
$del = isset($_GET['del']) ? (int)$_GET['del'] : 0;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';

$news_field = "id,rule_id,title,description,thumbnail,photo_album,content,add_time,is_show,allow_access,seo_title,seo_keywords,seo_description"; //要获取的页面字段
$news_field_array = [];
foreach (explode(',', $news_field) as $item) {
    $news_field_array[] = "n.{$item}";
}
$news_field = implode(',', $news_field_array) . ",trr.static_url,trr.template_id,trr.is_custom";
// 构建基础SQL和参数
$sql = "SELECT {$news_field} FROM news n INNER JOIN tb_rewrite_rules trr ON n.rule_id=trr.id WHERE n.classid IS NULL AND n.is_delete = ?";
$params = array($del);
$types = "i";

// 添加关键词条件
if (!empty($keywords)) {
    $sql .= " AND (n.title LIKE ? OR n.description LIKE ? OR n.content LIKE ? OR n.tags LIKE ?)";
    $keywords_like = "%{$keywords}%";
    $params = array_merge($params, [$keywords_like, $keywords_like, $keywords_like, $keywords_like]);
    $types .= "ssss";
}

// 添加排序
$sql .= " ORDER BY n.is_show ASC, n.paixu DESC, n.id DESC";

// 1. 获取总记录数
$stmt_total = $link->prepare($sql);
if (!$stmt_total) {
    echo $jsonData->jsonData(400, '数据库查询准备失败！');
    exit;
}

if ($types != "i") {
    $stmt_total->bind_param($types, ...$params);
} else {
    $stmt_total->bind_param($types, $params[0]);
}

if ($stmt_total->execute()) {
    $res_total = $stmt_total->get_result();
    $total = $res_total->num_rows;
    $stmt_total->close();
    // 2. 获取分页数据
    $start = ($currentpage - 1) * $pagesize;
    $sql .= " LIMIT ?, ?";
    $params[] = $start;
    $params[] = $pagesize;
    $types .= "ii";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        $code = 500;
        $message = '数据库查询准备失败';
        echo $jsonData->jsonData($code, $message, $obj);
        die();
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $code = 200;
    $message = '查询成功！';
    $obj["total"] = $total;

    while ($row = $res->fetch_assoc()) {
        // 处理数据
        $row["classid"] = empty($row["classid"]) ? '' : json_decode($row["classid"]);
        $row["template_id"] = (string)$row["template_id"];
        $row["is_custom"] = ($row["is_custom"] == '2'); //将是否时自定义路由转换为布尔值
        $row["thumbnail"] = empty($row["thumbnail"]) ? '' : json_decode($row["thumbnail"]);
        $row["photo_album"] = empty($row["photo_album"]) ? '' : json_decode($row["photo_album"]);
        $row["allow_access"] = empty($row["allow_access"]) ? '' : json_decode($row["allow_access"]);
        // $row["edit_show"] = true; //控制后台列表页，是显示文本，还是编辑框
        $obj["data"][] = $row;
    }
    $stmt->close();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);