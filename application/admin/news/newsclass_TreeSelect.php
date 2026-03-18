<?php
//获取文章分类信息
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

$newslist = isset($_GET['newslist']) ? (int)$_GET['newslist'] : 0;
$id_current = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$news_class_field = "id,parentid,title,description,thumbnail,banner,content,add_time,is_show,paixu,is_delete,detail_template_id,rule_id,show_type,allow_access,seo_title,seo_keywords,seo_description";
// 获取顶级分类
$sql = "SELECT {$news_class_field} FROM news_class WHERE parentid = 0 AND is_delete = 0 ORDER BY paixu DESC, id ASC";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败';
    echo $jsonData->jsonData($code, $message, $obj);
    die();
}

$stmt->execute();
$res = $stmt->get_result();

if ($res) {
    $code = 200;
    $message = 'success';
    $obj["data"] = array();

    while ($row = $res->fetch_assoc()) {
        $disabled = ($row["id"] == $id_current);
        $children = getChild($row["id"], $id_current, $disabled, $newslist, $news_class_field);

        if ($newslist == 1) {
            $newsCount = getNewsNumber($row["id"]);
            $row["title"] = $row["title"] . "(" . $newsCount . ")";
        }

        $obj["data"][] = processRow($row, $disabled, $children);
    }
    $stmt->close();
}

/**
 * 获取子分类
 */
function getChild($id, $id_cur, $child_state, $newslist, $news_class_field)
{
    global $link;

    $obj_child = array();
    $sql = "SELECT {$news_class_field} FROM news_class WHERE is_delete = 0 AND parentid = ? ORDER BY paixu DESC, id ASC";
    $stmt = $link->prepare($sql);

    if (!$stmt) {
        return $obj_child;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res_child = $stmt->get_result();

    if ($res_child) {
        while ($row = $res_child->fetch_assoc()) {
            $disabled = $child_state || ($row["id"] == $id_cur);
            $children = getChild($row["id"], $id_cur, $disabled, $newslist, $news_class_field);

            if ($newslist == 1) {
                $newsCount = getNewsNumber($row["id"]);
                $row["title"] = $row["title"] . "(" . $newsCount . ")";
            }

            $obj_child[] = processRow($row, $disabled, $children);
        }
        $stmt->close();
    }

    return $obj_child;
}

/**
 * 获取分类下的文章数量
 */
function getNewsNumber($classid)
{
    global $link;

    $sql = "SELECT COUNT(id) AS num FROM news WHERE is_delete = 0 AND classid LIKE ?";
    $stmt = $link->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $search = '%"' . $classid . '"%';
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $res_number = $stmt->get_result();
    $number = 0;

    if ($res_number) {
        $row = $res_number->fetch_assoc();
        $number = $row["num"] ?? 0;
        $stmt->close();
    }

    return $number;
}

mysqli_close($link);

// 处理行数据的公共函数
function processRow($row, $disabled, $children): array
{
    return [
        "value" => (string)$row["id"],
        "label" => $row["title"],
        "detail_template_id" => $row["detail_template_id"],
        "disabled" => $disabled,
        "children" => $children
    ];
}

echo $jsonData->jsonData($code, $message, $obj);