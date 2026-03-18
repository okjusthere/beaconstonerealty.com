<?php
//获取文章分类信息
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

$obj["total"] = 0; //总记录数
$obj["pagesize"] = 0; //总页数
$obj["currentpage"] = 1; //当前页
$obj["data"] = array(); //数据列表

try {
    $where = ""; //where语句
    $params = []; //请求参数集合
    $types = ""; //请求参数类型集合

    if (!empty($id)) {
        $wParentid = "";
        if (is_array($id)) {
            $id = \basic\Basic::filterStr($id);
            $params = $id;
            $types .= str_repeat('i', count($id));
            $placeholders = array_fill(0, count($id), '?');
            $strParentid = implode(',', $placeholders);  //将传递过来的ID数组，通过implode函数转换成字符串用逗号隔开
            $wParentid = " pc.parentid in({$strParentid})";
        } else {
            $id = \basic\Basic::filterInt($id);
            $wParentid = " pc.parentid=?";
            $params[] = $id;
            $types .= "i";
        }
        $where = " AND{$wParentid}";
    } else {
        $where = " AND pc.parentid=0";
    }

    $product_class_field = processTableField("id,parentid,title,description,thumbnail,banner,content", "pc"); //要获取的产品分类字段
    //sql查询语句
    $sql = "SELECT {$product_class_field},trr.static_url AS url FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id = trr.id WHERE pc.is_delete=0 AND pc.is_show = 1{$where} ORDER BY pc.paixu DESC,pc.id ASC";

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
        $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
        $row["banner"] = getPhotoAlbumPath(object_array(json_decode($row["banner"])));
        $row["url"] = processURL($row["url"]);

        $row["children"] = getChild($link, $row["id"]);
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

//获取子分类
function getChild(mysqli $link, $id = 0)
{
    $number = 0;

    $sql = "SELECT pc.id FROM product_class pc INNER JOIN tb_rewrite_rules trr ON pc.rule_id = trr.id WHERE pc.is_delete=0 AND pc.is_show=1 AND pc.parentid = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $number = $result->num_rows;
    }
    $stmt->close();

    return $number;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
