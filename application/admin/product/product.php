<?php
//获取产品表信息
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
if (!my_power("product_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

$currentpage = isset($_GET['currentPage']) ? (int)$_GET['currentPage'] : 1; //第几页
$pagesize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10; //每页显示条数
$del = isset($_GET['del']) ? (int)$_GET['del'] : 0; //删除状态
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : ''; //搜索关键词
$classid = isset($_GET['classid']) ? trim($_GET['classid']) : ''; //产品分类ID

$product_field = "id,rule_id,classid,title,specifications,origin,price,keywords,description,thumbnail,enclosure,photo_album,content,tags,add_time,is_show,is_top,paixu,view,allow_access,seo_title,seo_keywords,seo_description"; //要获取的产品字段
$product_field_array = [];
foreach (explode(',', $product_field) as $item) {
    $product_field_array[] = "p.{$item}";
}
$product_field = implode(',', $product_field_array) . ",trr.static_url,trr.template_id,trr.is_custom";
// 构建基础SQL和参数
$baseSql = "SELECT {$product_field} FROM product p INNER JOIN tb_rewrite_rules trr ON p.rule_id=trr.id WHERE p.is_delete = ?";
$params = array($del);
$types = "i"; // is_delete是整数

// 添加搜索条件
if (!empty($keywords)) {
    $searchTerm = "%{$keywords}%";
    $baseSql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.content LIKE ? OR p.tags LIKE ?)";
    $types .= "ssss";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

// 添加分类条件
if (!empty($classid)) {
    $classTerm = "%\"{$classid}\"%";
    $baseSql .= " AND p.classid LIKE ?";
    $types .= "s";
    array_push($params, $classTerm);
}

// 排序条件
$orderSql = " ORDER BY p.is_show ASC, p.is_top DESC, p.paixu DESC, p.id DESC";

// 获取总数
$countSql = $baseSql;
$stmt = $link->prepare($countSql);

if (!$stmt) {
    echo $jsonData->jsonData(400, '数据库查询准备失败！', []);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->num_rows;
$stmt->close();
$result->free();

// 获取分页数据
$start = ($currentpage - 1) * $pagesize;
$dataSql = $baseSql . $orderSql . " LIMIT ?, ?";
$types .= "ii";
array_push($params, $start, $pagesize);

$stmt = $link->prepare($dataSql);

if (!$stmt) {
    echo $jsonData->jsonData(400, '分页查询预处理失败！', []);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$code = 200;
$message = 'success';
$obj["data"] = [];
$obj["total"] = $total;

while ($row = $result->fetch_assoc()) {
    $row["classid"] = json_decode($row["classid"]);
    $row["template_id"] = (string)$row["template_id"];
    $row["is_custom"] = ($row["is_custom"] == '2');
    $row["is_top"] = ($row["is_top"] !== '1');
    $row["thumbnail"] = json_decode($row["thumbnail"]);
    $row["enclosure"] = json_decode($row["enclosure"]);
    $row["photo_album"] = json_decode($row["photo_album"]);
    $row["allow_access"] = empty($row["allow_access"]) ? '' : json_decode($row["allow_access"]);
    $row["edit_show"] = true;

    $obj["data"][] = $row;
}

$stmt->close();
$result->free();

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);