<?php
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

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("proclass_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 获取查询参数
$del = isset($_GET['del']) ? (int)$_GET['del'] : 0;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';

// 构建WHERE条件
$where = " WHERE is_delete=?";
$params = array($del);
$types = "i";

if (!empty($keywords)) {
    $where .= " AND (pc.title LIKE ? OR pc.description LIKE ?)";
    $searchTerm = "%{$keywords}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
} elseif ($del == 0) {
    $where .= " AND pc.parentid=0";
}

//要获取的产品分类字段
$select_field = processTableField("id,parentid,title,description,thumbnail,banner,content,add_time,is_show,paixu,is_delete,detail_template_id,rule_id,show_type,allow_access,seo_title,seo_keywords,seo_description", "pc");
// 主查询
$sql = "SELECT {$select_field},trr.static_url,trr.template_id,trr.is_custom FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id=trr.id {$where} ORDER BY pc.is_show ASC, pc.paixu DESC, pc.id ASC";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 动态绑定参数
    if (!empty($keywords)) {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param($types, $del);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $code = 200;
        $message = 'success';

        while ($row = $result->fetch_assoc()) {
            $row = processRow($row);
            if (empty($row["parentid"]) && empty($keywords) && $del == 0) {
                $row["children"] = getChild($row["id"], $select_field);
            }
            $obj["data"][] = $row;
        }

        $stmt->close();
    } else {
        $code = 100;
        $message = '查询执行失败: ' . $stmt->error;
    }
} else {
    $code = 100;
    $message = 'SQL预处理失败: ' . $link->error;
}

// 获取子分类函数
function getChild($id, $select_field): array
{
    global $link;
    $obj_child = array();

    $sql = "SELECT {$select_field},trr.static_url,trr.template_id,trr.is_custom FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id=trr.id WHERE is_delete=0 AND pc.parentid=? ORDER BY pc.is_show ASC, pc.paixu DESC, pc.id ASC";
    $stmt = $link->prepare($sql);

    if ($stmt && $stmt->bind_param('i', $id) && $stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row = processRow($row);
            $row["children"] = getChild($row["id"], $select_field);
            $obj_child[] = $row;
        }
        $stmt->close();
    }

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
    $row["show_type"] = (int)$row["show_type"];
    return $row;
}

mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);