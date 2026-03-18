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

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$id = isset($_POST['id']) ? $_POST['id'] : ''; //产品分类ID（可以是单个的ID，也可以是ID数组）
$displays = isset($_POST['displays']) ? \basic\Basic::filterInt($_POST['displays']) : 1; //每页显示的个数
$currentpage = isset($_POST['currentpage']) ? \basic\Basic::filterInt($_POST['currentpage']) : 1; //当前页码
$local_attr = $_POST['local_attr'] ?? ''; //筛选选中的产品属性值ID

$obj["total"] = 0; //总记录数
$obj["pagesize"] = 0; //总页数
$obj["currentpage"] = 1; //当前页
$obj["data"] = array(); //数据列表

try {
    $where = ""; //where语句
    $params = []; //请求参数集合
    $types = ""; //请求参数类型集合

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
        if ($wClassid != " ()") $where .= " and{$wClassid}";
    }

    //当启用筛选时，获取有多少符合条件的产品ID
    if (!empty($local_attr)) {
        $l_attr_array = explode(',', $local_attr);
        //将传递过来的属性值，按照分类分成对应的数组
        $grouping = array();
        foreach ($l_attr_array as $key => $value) {
            if (!empty($grouping[checkAttributeClassID($link, $value)])) {
                $grouping[checkAttributeClassID($link, $value)] = $grouping[checkAttributeClassID($link, $value)] . "," . $value;
            } else {
                $grouping[checkAttributeClassID($link, $value)] = $value;
            }
        }
        $params_attr = []; //请求参数集合
        $types_attr = ""; //请求参数类型集合
        $where_attr = ""; //where语句
        $grouping = array_values($grouping); //因为数组的键是具体的整数，不是自增，所以用array_values将数组进行重新排列组合
        foreach ($grouping as $key => $value) {
            $attr_array = explode(',', $value);
            foreach ($attr_array as $k => $val) {
                $where_attr .= $k == 0 ? " (" : "";
                $where_attr .= "attribute_value_id LIKE ?";
                $where_attr .= $k == count($attr_array) - 1 ? ")" : " OR ";
                $params_attr[] = '%\"{$val}\"%';
                $types_attr .= 's';
            }
            $where_attr .= $key == count($grouping) - 1 ? "" : " AND";
        }

        $suit_id = array(); //符合筛选条件的产品ID数组
        $sql_attribute = "SELECT product_id FROM tb_product_attribute WHERE{$where_attr}";
        //准备SQL语句
        $stmt_attr = $link->prepare($sql_attribute);
        //绑定参数
        $stmt_attr->bind_param($types_attr, ...$params_attr);
        //执行查询
        if ($stmt_attr->execute()) {
            $result_attr = $stmt_attr->get_result(); //查询结果
            while ($row = $result_attr->fetch_assoc()) {
                $suit_id[] = $row["product_id"];
            }
        }

        if (count($suit_id) > 0) {
            $suit_id_ary = array_unique($suit_id);
            $ids = implode(',', array_fill(0, count($suit_id_ary), '?')); //生成?占位符
            $params[] = array_merge($params, $suit_id_ary);
            $types .= str_repeat('i', count($suit_id_ary));
            $where .= " AND id IN({$ids})";
        } else { //当属性ID不为空时，如果没有符合条件的产品ID，不再继续往下执行，程序终止
            $code = 200;
            $message = 'success';
            $obj["data"] = array();
            echo $jsonData->jsonData($code, $message, $obj);
            exit();
        }
    }


    $product_field = processTableField("id,classid,title,specifications,origin,price,keywords,description,thumbnail,enclosure,photo_album,add_time,view", "p"); //要获取的产品字段
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
            "p.is_top DESC, p.paixu DESC, p.id DESC";

    //准备SQL语句，查询符合条件的总条数
    $stmt_total = $link->prepare($sql);
    if (!$stmt_total) throw new Exception('查询总条数SQL准备失败' . $link->error);
    //绑定参数
    if (!empty($types) && strlen($types) === count($params)) $stmt_total->bind_param($types, ...$params);
    // 执行查询
    if (!$stmt_total->execute()) throw new Exception('总记录查询出错：' . $link->error);
    //获取查询结果
    $result_total = $stmt_total->get_result();

    //获取总条数-符合条件的总记录数
    $total = $result_total->num_rows;
    $pagesize = ceil($total / $displays); //一共有多少页，ceil向上取整，有小数就加1
    $currentpage = $currentpage > $pagesize ? 1 : $currentpage;
    $start = ($currentpage - 1) * $displays; //从第几条数据开始读取
    $limit = " limit ?,?"; //数据查询范围
    $types .= "ii"; //绑定翻页参数类型
    $params = array_merge($params, [$start, $displays]); //绑定翻页参数

    //准备SQL语句，查询对应页的记录
    $stmt = $link->prepare($sql . $limit);
    if (!$stmt) throw new Exception('查询SQL准备失败' . $link->error);
    //绑定参数
    if (!empty($types) && strlen($types) === count($params)) $stmt->bind_param($types, ...$params);
    //执行查询
    if (!$stmt->execute()) throw new Exception('查询出错：' . $link->error);
    //获取查询结果
    $result = $stmt->get_result();

    $obj["total"] = $total; //总记录数
    $obj["pagesize"] = $pagesize; //总页数
    $obj["currentpage"] = $currentpage; //当前页

    while ($row = $result->fetch_assoc()) {
        $row["classid"] = json_decode($row["classid"], true);
        $row["thumbnail"] = getThumbnailPath(json_decode($row["thumbnail"], true));
        $row["enclosure"] = getEnclosurePath(json_decode($row["enclosure"], true));
        $row["photo_album"] = getPhotoAlbumPath(json_decode($row["photo_album"], true));
        $row["url"] = processURL($row["url"]);
        // 处理合并后的自定义字段数据
        $row["field"] = processFieldInfo($row["field_data"]);
        unset($row["field_data"]);

        $obj["data"][] = $row;
    }

    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt_attr) && $stmt_attr instanceof mysqli_stmt) {
        $stmt_attr->close();
    }
    if (isset($stmt_total) && $stmt_total instanceof mysqli_stmt) {
        $stmt_total->close();
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

/*当访问为数组时，做一个查询拼接
@param arry classidary：文章分类ID组成的数组*/
function classidWhere($classidary, &$params, &$types): string
{
    $placeholders = [];
    $w = " (";
    foreach ($classidary as $key => $value) {
        if (\basic\Basic::filterInt($value) > 0) {
            $w .= $key > 0 ? " or " : "";
            $w .= "classid like ?";
            $placeholders[] = "%\"{$value}\"%";
            $types .= "s";
        }
    }
    $w .= ")";
    $params = array_merge($params, $placeholders);
    return $w;
}

//检查产品属性ID所属的分类ID
function checkAttributeClassID(mysqli $link, $id): int
{
    $class_id = 0;
    $sql = "select attribute_class from tb_product_attribute_value where id = ?";
    $stmt = $link->prepare($sql); //准备SQL语句
    $stmt->bind_param('i', $id); //绑定参数
    if ($stmt->execute()) {
        $result = $stmt->get_result(); //获取查询结果
        $class_id = ($result->fetch_assoc())["attribute_class"];
    }
    //关闭语句
    $stmt->close();

    return $class_id;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
