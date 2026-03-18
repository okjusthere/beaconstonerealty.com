<?php
//获取文章表信息
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

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("news_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit;
}

//初始化变量
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1; //第几页
$pagesize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 10; //每页显示条数
$del = isset($_GET['del']) ? intval($_GET['del']) : 0; //删除状态
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : ''; //搜索关键词
$classid = isset($_GET['classid']) ? trim($_GET['classid']) : ''; //文章分类ID

$news_field = "id,classid,rule_id,title,url,keywords,description,thumbnail,enclosure,photo_album,content,tags,add_time,is_show,is_top,paixu,view,allow_access,seo_title,seo_keywords,seo_description"; //要获取的文章字段
$news_field_array = [];
foreach (explode(',', $news_field) as $item) {
    $news_field_array[] = "n.{$item}";
}
$news_field = implode(',', $news_field_array) . ",trr.static_url,trr.template_id,trr.is_custom";
// 构建基础SQL和参数
$sql = "SELECT {$news_field} FROM news n INNER JOIN tb_rewrite_rules trr ON n.rule_id=trr.id WHERE n.classid IS NOT NULL AND n.is_delete = ?";
$params = array($del);
$types = "i"; // 参数类型: i=integer

// 添加关键词条件
if (!empty($keywords)) {
    $keywords_like = "%{$keywords}%";
    $sql .= " AND (n.title LIKE ? OR n.description LIKE ? OR n.content LIKE ? OR n.tags LIKE ?)";
    $params = array_merge($params, [$keywords_like, $keywords_like, $keywords_like, $keywords_like]);
    $types .= "ssss";
}

// 添加分类ID条件
if (!empty($classid)) {
    $classid_like = "%\"{$classid}\"%";
    $sql .= " AND n.classid LIKE ?";
    $params[] = $classid_like;
    $types .= "s";
}

// 添加排序
$sql .= " ORDER BY n.is_show ASC, n.is_top DESC, n.paixu DESC, n.id DESC";

// 准备查询总条数的语句
$stmt_total = $link->prepare($sql);
if (!$stmt_total) {
    echo $jsonData->jsonData(400, '数据库查询准备失败！');
    exit;
}

// 绑定参数
if ($types != "i") {
    $stmt_total->bind_param($types, ...$params);
} else {
    $stmt_total->bind_param($types, $params[0]);
}

// 执行查询
$stmt_total->execute();
$res_total = $stmt_total->get_result();
$total = $res_total->num_rows;
$stmt_total->close();
// 添加分页限制
$start = ($currentpage - 1) * $pagesize;
$sql .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $pagesize;
$types .= "ii";

// 准备分页查询语句
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo $jsonData->jsonData(400, '数据库查询准备失败！', []);
    exit;
}

// 绑定参数
$stmt->bind_param($types, ...$params);

// 执行查询
$stmt->execute();
$res = $stmt->get_result();

$code = 200;
$message = 'success';
$obj["data"] = [];
$obj["total"] = $total;    //记录总条数

while ($row = $res->fetch_assoc()) {
    $row["classid"] = json_decode($row["classid"]);
    $row["template_id"] = (string)$row["template_id"]; //将是否时自定义路由转换为布尔值
    $row["is_custom"] = ($row["is_custom"] == '2');
    //将是否置顶状态由数字转换成bool值
    $row["is_top"] = ($row["is_top"] != '1');
    $row["thumbnail"] = json_decode($row["thumbnail"]);
    $row["enclosure"] = json_decode($row["enclosure"]);
    $row["photo_album"] = json_decode($row["photo_album"]);
    $row["allow_access"] = empty($row["allow_access"]) ? '' : json_decode($row["allow_access"]);

    $obj["data"][] = $row;
}

$stmt->close();

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);