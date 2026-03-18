<?php
//获取栏目各个列表对应的数据
include_once '../checking_user.php'; // 查看是否有访问权限
include_once '../../../wf-config.php'; // 链接数据库
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; // 获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  // 响应码
$message = 'success';  // 响应信息
$obj = ['data' => []]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';//搜索关键词

$obj['data']['page'] = getPageInfo($link); //页面
$obj['data']['news'] = getNewsInfo($link); //文章
$obj['data']['news_class'] = getNewsClassInfo($link); //文章分类
$obj['data']['product'] = getProducInfo($link); //产品
$obj['data']['product_class'] = getProductClassInfo($link); //产品分类

//关闭数据库链接
mysqli_close($link);

//获取页面信息
function getPageInfo(mysqli $link): array
{
    $data = []; //返回数据

    $sql = "SELECT n.id,n.title FROM news n INNER JOIN tb_rewrite_rules trr ON n.rule_id = trr.id WHERE n.classid IS NULL AND n.is_delete = 0 AND n.is_show = '1' ORDER BY n.paixu DESC,n.id DESC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = processSelectData((string)$row["id"], $row["title"]);
                }
            }
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//获取文章信息
function getNewsInfo(mysqli $link, string $keywords = ''): array
{
    $data = []; //返回数据

    //$where = !empty($keywords) ? " AND title like ?" : "";
    //$limit = empty($keywords) ? " LIMIT 0,20" : ""; //当没有搜索关键词的时候，最多显示前20条文章
    $sql = "SELECT n.id,n.title FROM news n INNER JOIN tb_rewrite_rules trr ON n.rule_id = trr.id WHERE classid IS NOT NULL AND n.is_delete = 0 AND n.is_show = '1' ORDER BY n.is_top DESC,n.paixu DESC,n.id DESC";

    $stmt = $link->prepare($sql);
    if ($stmt) {
        //当搜索关键词不为空时，绑定参数
        if (!empty($keywords)) {
            $key = "%{$keywords}%";
            $stmt->bind_param('s', $key);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = processSelectData((string)$row["id"], $row["title"]);
                }
            }
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//获取文章分类信息
function getNewsClassInfo(mysqli $link, int $id = 0, int $maxDepth = 10): array
{
    if ($maxDepth <= 0) {
        return []; // 防止无限递归
    }

    $data = []; //返回数据
    $sql = "SELECT nc.id,nc.title FROM news_class nc INNER JOIN tb_rewrite_rules trr ON nc.rule_id = trr.id WHERE nc.parentid = ? AND nc.is_delete = 0 AND nc.is_show = '1' ORDER BY nc.paixu DESC,nc.id ASC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row["children"] = getNewsClassInfo($link, $row["id"], $maxDepth - 1);
                    $data[] = processSelectData((string)$row["id"], $row["title"], $row["children"]);
                }
            }
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//获取产品信息
function getProducInfo(mysqli $link, string $keywords = ''): array
{
    $data = []; //返回数据

//    $where = !empty($keywords) ? " AND title like ?" : "";
//    $limit = empty($keywords) ? " LIMIT 0,20" : ""; //当没有搜索关键词的时候，最多显示前20个产品
    $sql = "SELECT p.id,p.title FROM product p INNER JOIN tb_rewrite_rules trr ON p.rule_id = trr.id WHERE p.is_delete = 0 AND p.is_show = '1' ORDER BY p.is_top DESC,p.paixu DESC,p.id DESC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        //当搜索关键词不为空时，绑定参数
        if (!empty($keywords)) {
            $key = "'%{$keywords}%'";
            $stmt->bind_param('s', $key);
        }
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = processSelectData((string)$row["id"], $row["title"]);
                }
            }
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//获取产品分类信息
function getProductClassInfo(mysqli $link, int $id = 0, int $maxDepth = 10): array
{
    if ($maxDepth <= 0) {
        return []; // 防止无限递归
    }

    $data = []; //返回数据
    $sql = "SELECT pc.id,pc.title FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id = trr.id WHERE pc.parentid = ? AND pc.is_delete = 0 AND pc.is_show = '1' ORDER BY pc.paixu DESC,pc.id ASC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row["children"] = getProductClassInfo($link, $row["id"], $maxDepth - 1);
                    $data[] = processSelectData((string)$row["id"], $row["title"], $row["children"]);
                }
            }
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//处理返回给前端的select下拉信息
function processSelectData($value, $label, array $children = []): array
{
    return [
        "value" => $value,
        "label" => $label,
        "children" => $children
    ];
}

echo $jsonData->jsonData($code, $message, $obj);