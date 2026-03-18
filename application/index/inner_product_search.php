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
$keywords = isset($_POST['keywords']) ? \basic\Basic::filterStr($_POST['keywords']) : ''; //产品搜索关键词
$displays = isset($_POST['displays']) ? \basic\Basic::filterInt($_POST['displays']) : -1; //每页显示的个数
$currentpage = isset($_POST['currentpage']) ? \basic\Basic::filterInt($_POST['currentpage']) : 1; //当前页码

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
            $wClassid = classidWhere($id);
        } else {
            $id = \basic\Basic::filterInt($id);
            $claid = eliminateID(getProductClassChildID($link, $id) . $id, 'product_class'); //过滤掉没有访问权限的产品分类id
            $wClassid = classidWhere(explode(',', $claid), $params, $types);
        }
        if ($wClassid != " ()") $where .= " AND{$wClassid}";
    }

    //关键词搜索语句拼接
    if (!empty($keywords)) {
        $where .= " AND (p.title LIKE ? OR p.keywords LIKE ? OR p.description LIKE ? OR p.content LIKE ?)";
        $k = "%{$keywords}%";
        $types .= "ssss"; //绑定参数类型
        $params = array_merge($params, [$k, $k, $k, $k]); //绑定参数
    }

    $product_field = processTableField("id,classid,title,specifications,origin,price,keywords,description,thumbnail,enclosure,photo_album,add_time,view", "p"); //要获取的产品字段
    //sql查询语句
    $sql = "SELECT {$product_field},".
            "trr.static_url AS url,".
            "GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||') as field_data ".
        "FROM ".
            "product p "
                ."INNER JOIN tb_rewrite_rules trr ON p.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'product' AND fi.record_id = p.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'product' AND fc.field_name = fi.field_name ".
        "WHERE ".
            "p.is_delete=0 ".
            "AND p.is_show=1{$where} ".
        "GROUP BY p.id ".
        "ORDER BY ".
            "p.is_top DESC,p.paixu DESC,p.id DESC";

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
    if (isset($stmt_total) && $stmt_total instanceof mysqli_stmt) {
        $stmt_total->close();
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

/*当访问为数组时，做一个查询拼接
@param arry classidary：文章分类ID组成的数组*/
function classidWhere($classidary)
{
    $len = count($classidary);//查看有几个ID
    $w = "";
    $w .= " (";
    foreach ($classidary as $key => $value) {
        $w .= $len > 1 && $key != 0 ? " or " : "";
        $w .= "classid like '%\"{$value}\"%'";
    }
    $w .= ")";
    return $w;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
