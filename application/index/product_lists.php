<?php
//获取新闻表信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//引用自定义函数
include_once "function.php";

//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

header('Content-Type:application/json; charset=utf-8');

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

$param = isset($_POST['param']) ? \basic\Basic::filterStr($_POST['param']) : '';
if (empty($param)) {
    echo $jsonData->jsonData(400, '无效的请求数据', []);
    exit;
}
$paramAry = json_decode($param, true); //将参数从string转换成数组

$obj["data"] = []; //产品数据

foreach ($paramAry as $value) {
    $id = is_array($value["id"]) ? implode('|', $value["id"]) : $value["id"];
    $row["request_key"] = "product{$id}|{$value["top"]}|{$value["type"]}";
    $row["list"] = getProductList($link, $value["id"], $value["top"]);
    $obj["data"][] = $row;
}

$code = 200;  //响应码
$message = 'success';  //响应信息

/* 获取对应分类下的产品数据
 * @param mysqli $link 链接数据库
 * @param int id 产品分类ID
 * @param int top 要查询的产品个数
 */
function getProductList(mysqli $link, $id, $top = -1)
{
    $data = []; //返回的数据
    $where = "";
    $params = []; //参数集合
    $types = ""; //参数类型集合

    if (!empty($id)) {
        $wClassid = "";
        if (is_array($id)) {
            $id = \basic\Basic::filterStr($id);
            $id = explode(',', eliminateID(implode(',', $id), 'product_class')); //过滤掉没有访问权限的产品分类id
            $wClassid = classidWhere($id, $params, $types);
        } else {
            $id = \basic\Basic::filterInt($id);
            $claid = eliminateID(getProductClassChildID($link, $id) . $id, 'product_class'); //过滤掉没有访问权限的文章分类id
            $wClassid = classidWhere(explode(',', $claid), $params, $types);
        }
        if ($wClassid != " ()") $where .= " AND{$wClassid}";
    }

    $limit = "";
    if ($top > 0) {
        $limit = " LIMIT 0,?";
        $params[] = $top;
        $types .= "i";
    }

    $product_field = processTableField('id,classid,title,specifications,origin,price,keywords,description,thumbnail,enclosure,photo_album,add_time', 'p'); //要获取的产品字段
    //sql查询语句
    $sql = "SELECT {$product_field},".
            "trr.static_url AS url,".
            "GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||') as field_data ".
        "FROM ".
            "product p ".
                "INNER JOIN tb_rewrite_rules trr ON p.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'product' AND fi.record_id = p.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'product' AND fc.field_name = fi.field_name ".
        "WHERE ".
            "p.is_delete=0 ".
            "AND p.is_show=1{$where} ".
        "GROUP BY p.id ".
        "ORDER BY ".
            "p.is_top DESC,p.paixu DESC,p.id DESC".
        "{$limit}";

    // 准备预处理语句
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        $stmt->close(); //关闭语句
        return $data;
    }

    // 绑定参数
    if (!empty($types) && count($params) > 0) $stmt->bind_param($types, ...$params);

    // 执行查询
    if (!$stmt->execute()) {
        $stmt->close(); //关闭语句
        return $data;
    }

    // 获取结果
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row["classid"] = object_array(json_decode($row["classid"]));
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $row["enclosure"] = getEnclosurePath(object_array(json_decode($row["enclosure"])));
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            $row["url"] = processURL($row["url"]);
            // 处理合并后的自定义字段数据
            $row["field"] = processFieldInfo($row["field_data"]);
            unset($row["field_data"]);

            $data[] = $row;
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

/*当访问为数组时，做一个查询拼接
@param arry classidary：产品分类ID组成的数组*/
function classidWhere($classidary, &$params, &$types): string
{
    $placeholders = [];
    $w = " (";
    foreach ($classidary as $key => $value) {
        $num = (int)$value;
        if (\basic\Basic::filterInt($num) > 0) {
            $w .= $key > 0 ? " OR " : "";
            $w .= "p.classid LIKE ?";
            $placeholders[] = "%\"{$num}\"%";
            $types .= "s";
        }
    }
    $w .= ")";
    $params = array_merge($params, $placeholders);
    return $w;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
