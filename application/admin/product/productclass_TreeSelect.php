<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$productlist = isset($_GET['productlist']) ? trim($_GET['productlist']) : 0; //是否是产品列表页在引用
$id_current = isset($_GET['id']) ? intval($_GET['id']) : 0; //禁止选中的分类ID

$select_field = processTableField("id,parentid,title,description,thumbnail,banner,content,add_time,is_show,paixu,is_delete,detail_template_id,rule_id,show_type,allow_access,seo_title,seo_keywords,seo_description", "pc");
// 获取顶级分类
$sql = "SELECT {$select_field},trr.static_url,trr.template_id,trr.is_custom FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id=trr.id WHERE pc.parentid=0 AND pc.is_delete=0 ORDER BY pc.paixu DESC, pc.id ASC";
$stmt = $link->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();

    $code = 200;
    $message = 'success';
    $obj["data"] = array();

    while ($row = $res->fetch_assoc()) {
        $disabled = ($row["id"] == $id_current);
        // 通过当前ID查询相关子分类
        $children = getChild($row["id"], $id_current, $disabled, $productlist);

        if ($productlist == 1) {
            $row["title"] = $row["title"] . "(" . getProductNumber($row["id"]) . ")";
        }

        $obj["data"][] = processRow($row, $disabled, $children);
    }
    $stmt->close();
}

/*
 * $id 父级分类ID
 * $id_cur 禁止选中的分类ID
 * $child_state 父级分类是否选中true/false
 * $productlist 判断产品列表是否在引用（1：是，0：不是）
 */
function getChild($id, $id_cur, $child_state, $productlist)
{
    global $link;
    $obj_child = array();

    $sql = "SELECT * FROM product_class WHERE is_delete=0 AND parentid=? ORDER BY paixu DESC, id ASC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res_child = $stmt->get_result();

        while ($row = $res_child->fetch_assoc()) {
            $disabled = $child_state ? true : ($row["id"] == $id_cur);
            $children = getChild($row["id"], $id_cur, $disabled, $productlist);

            if ($productlist == 1) {
                $row["title"] = $row["title"] . "(" . getProductNumber($row["id"]) . ")";
            }

            $obj_child[] = processRow($row, $disabled, $children);
        }
        $stmt->close();
    }
    return $obj_child;
}

/*获取对应分类下有多少篇文章
 * @param int $classid 产品分类id*/
function getProductNumber(int $classid)
{
    global $link;
    $number = 0;

    $sql = "SELECT COUNT(id) AS num FROM product WHERE is_delete=0 AND classid LIKE ?";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        $search_term = '%"' . $classid . '"%';
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $res_number = $stmt->get_result();
        $row = $res_number->fetch_assoc();
        $number = $row["num"];
        $stmt->close();
    }
    return $number;
}

//关闭数据库链接
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