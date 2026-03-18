<?php
//获取新闻表信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';

include_once "function.php"; //引用自定义函数

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
    echo $jsonData->jsonData(400, '无效的请求数据');
    exit;
}
$paramAry = json_decode($param, true); //将参数从string转换成数组

$obj["data"] = []; //文章数据

foreach ($paramAry as $value) {
    $id = is_array($value["id"]) ? implode('|', $value["id"]) : $value["id"];
    $row["request_key"] = "news{$id}|{$value["top"]}|{$value["type"]}";
    $row["list"] = getNewsList($link, $value["id"], $value["top"]);
    $obj["data"][] = $row;
}

$code = 200;  //响应码
$message = 'success';  //响应信息

/* 获取对应分类下的文章数据
 * @param mysqli $link 链接数据库
 * @param int id 文章分类ID
 * @param int top 要查询的文章条数
 */
function getNewsList(mysqli $link, $id, $top = -1): array
{
    $data = []; //返回的数据
    $where = "";
    $params = []; //请求参数集合
    $types = ""; //请求参数类型集合

    if (!empty($id)) {
        $wClassid = "";
        if (is_array($id)) {
            $id = \basic\Basic::filterStr($id);
            $wClassid .= " (";
            $placeholders = [];

            foreach ($id as $key => $value) {
                if (is_numeric($value)) {
                    $wClassid .= $key > 0 ? " or " : "";
                    $wClassid .= "n.classid like ?";
                    $placeholders[] = "%\"{$value}\"%";
                    $types .= "s";
                }
            }
            $wClassid .= ")";
            $params = array_merge($params, $placeholders);
        } else {
            $id = \basic\Basic::filterInt($id);
            $claid = getNewsClassChildID($link, $id) . $id;
            $classIds = explode(',', $claid);

            $wClassid = " (";
            $placeholders = [];

            foreach ($classIds as $key => $classId) {
                if ($key > 0) {
                    $wClassid .= " or ";
                }
                $wClassid .= "n.classid like ?";
                $placeholders[] = "%\"{$classId}\"%";
                $types .= "s";
            }
            $wClassid .= ")";
            $params = array_merge($params, $placeholders);
        }
        $where = " and{$wClassid}";
    }

    $limit = "";
    if ($top > 0) {
        $limit = " limit 0,?";
        $params[] = $top;
        $types .= "i";
    }

    $news_field = processTableField('id,classid,title,url,keywords,description,thumbnail,enclosure,photo_album,add_time,content,view', 'n'); //要获取的文章字段
    //sql查询语句
    $sql = "SELECT ".
        "{$news_field},".
        "CASE ".
            "WHEN IFNULL(n.url, '') = '' THEN trr.static_url ".
            "ELSE n.url ".
            "END AS url,".
            "GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||') as field_data ".
        "FROM ".
            "news n ".
                "INNER JOIN tb_rewrite_rules trr ON n.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'news' AND fi.record_id = n.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'news' AND fc.field_name = fi.field_name ".
        "WHERE ".
            "n.classid IS NOT NULL ".
            "AND n.is_show=1 ".
            "AND n.is_delete=0{$where} ".
        "GROUP BY n.id ".
        "ORDER BY ".
            "n.is_top DESC,n.paixu DESC,n.id DESC".
        "{$limit}";

    // 准备预处理语句
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        $stmt->close(); //关闭语句
        return $data;
    }

    // 绑定参数
    if (!empty($types) && strlen($types) === count($params)) $stmt->bind_param($types, ...$params);

    // 执行查询
    if (!$stmt->execute()) {
        $stmt->close(); //关闭语句
        return $data;
    }

    // 获取结果
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row["classid"] = object_array(json_decode($row["classid"], true));
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $row["enclosure"] = getEnclosurePath(object_array(json_decode($row["enclosure"])));
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            $row["url"] = processURL($row["url"]);
            // 处理合并后的字段数据
            // $row["field"] = processFieldInfo($row["field_data"]);
            $row["field"] = getFieldInfo('news',$row["id"]);
            unset($row["field_data"]);

            $data[] = $row;
        }
    }

    // 关闭语句
    $stmt->close();

    return $data;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
