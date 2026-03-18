<?php
//获取文章分类表信息
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
if (!my_power("newsclass_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit; //终止继续执行
}

$del = isset($_GET['del']) ? (int)$_GET['del'] : 0; //删除状态
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : ''; //搜索关键词

//要获取的文章分类字段
$select_field = "id,parentid,title,description,thumbnail,banner,content,add_time,is_show,paixu,is_delete,detail_template_id,rule_id,show_type,allow_access,seo_title,seo_keywords,seo_description";
$select_field_array = [];
foreach (explode(',', $select_field) as $item) {
    $select_field_array[] = "nc.{$item}";
}
$select_field = implode(',', $select_field_array) . ",trr.static_url,trr.template_id,trr.is_custom";
// 构建SQL和参数
$sql = "SELECT {$select_field} FROM news_class nc INNER JOIN tb_rewrite_rules trr ON nc.rule_id=trr.id WHERE nc.is_delete = ?";
$params = array($del);
$types = "i";

if (!empty($keywords)) {
    $sql .= " AND (nc.title LIKE ? OR nc.description LIKE ? OR nc.content LIKE ?)";
    $keywords_like = "%{$keywords}%";
    $params = array_merge($params, [$keywords_like, $keywords_like, $keywords_like]);
    $types .= "sss";
} else if ($del == 0) {
    $sql .= " AND nc.parentid = 0";
}

$sql .= " ORDER BY nc.is_show ASC, nc.paixu DESC, nc.id ASC";

// 准备主查询
$stmt = $link->prepare($sql);
if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

// 绑定参数
if ($types != "i") {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $params[0]);
}

// 执行查询
$stmt->execute();
$res = $stmt->get_result();

if ($res) {
    $code = 200;
    $message = 'success';
    while ($row = $res->fetch_assoc()) {
        $row = processRow($row);
        if (empty($row["parentid"]) && empty($keywords) && $del == 0) {
            $row["children"] = getChild($row["id"], $select_field);
        }

        $obj["data"][] = $row;
    }
    $stmt->close();
}

function getChild($id, $news_class_field)
{
    global $link;

    $obj_child = array();
    $sql = "SELECT {$news_class_field} FROM news_class nc INNER JOIN tb_rewrite_rules trr ON nc.rule_id=trr.id WHERE nc.is_delete = 0 AND nc.parentid = ? ORDER BY nc.is_show ASC, nc.paixu DESC, nc.id ASC";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return $obj_child;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res_child = $stmt->get_result();

    if ($res_child && $res_child->num_rows > 0) {
        while ($row = $res_child->fetch_assoc()) {
            $row = processRow($row);
            $row["children"] = getChild($row["id"], $news_class_field);
            $obj_child[] = $row;
        }
    }
    $stmt->close();
    return $obj_child;
}

// 处理行数据的公共函数
function processRow($row)
{
    $row["parentid"] = ($row["parentid"] === 0) ? '' : (string)$row["parentid"];
    $row["template_id"] = (string)$row["template_id"];
    $row["detail_template_id"] = (string)$row["detail_template_id"];
    $row["is_custom"] = ($row["is_custom"] == '2');
    $row["thumbnail"] = json_decode($row["thumbnail"]);
    $row["banner"] = json_decode($row["banner"]);
    $row["allow_access"] = empty($row["allow_access"]) ? '' : json_decode($row["allow_access"]);
    $row["edit_title"] = false;
    return $row;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);