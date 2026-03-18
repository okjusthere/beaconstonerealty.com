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

$id = isset($_POST['id']) ? $_POST['id'] : ''; //文章分类ID（可以是单个的ID，也可以是ID数组）
$top = isset($_POST['top']) ? \basic\Basic::filterInt($_POST['top']) : -1; //读取的个数

$where = "";
if (!empty($id)) {
    $wClassid = "";
    if (is_array($id)) {
        $id = \basic\Basic::filterStr($id);
        $wClassid .= " (";
        foreach ($id as $key => $value) {
            if (is_numeric($value)) {
                $wClassid .= $key > 0 ? " or " : "";
                $wClassid .= "n.classid like '%\"{$value}\"%'";
            }
        }
        $wClassid .= ")";
    } else {
        $id = \basic\Basic::filterInt($id);
        $claid = getNewsClassChildID($link,$id) . $id;
        $wClassid = classidWhere(explode(',', $claid));
    }
    $where = " and{$wClassid}";
}
$limit = "";
if ($top > 0) {
    $limit = " limit 0,{$top}";
}

/*当访问为数组时，做一个查询拼接
@param arry classidary：文章分类ID组成的数组*/
function classidWhere($classidary):string
{
    $w = "";
    $w .= " (";
    foreach ($classidary as $key => $value) {
        if (\basic\Basic::filterInt($value) > 0) {
            $w .= $key > 0 ? " OR " : "";
            $w .= "n.classid LIKE '%\"{$value}\"%'";
        }
    }
    $w .= ")";
    return $w;
}

$news_field = processTableField('id,classid,title,url,keywords,description,thumbnail,enclosure,photo_album,add_time,content,view', 'n'); //要获取的文章字段
//sql查询语句
$sql = "SELECT ".
    "{$news_field},".
    "CASE ".
        "WHEN n.url IS NULL OR n.url='' THEN trr.static_url ".
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

//获取查询结果集
$res = my_sql($sql);

if ($res) {
    $code = 200;
    $message = 'success';
    $obj["data"] = array();
    if (mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row["classid"] = object_array(json_decode($row["classid"]));
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $row["enclosure"] = getEnclosurePath(object_array(json_decode($row["enclosure"])));
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            $row["url"] = processURL($row["url"]);
            // 处理合并后的字段数据
            $row["field"] = processFieldInfo($row["field_data"]);
            unset($row["field_data"]);

            $obj["data"][] = $row;
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
