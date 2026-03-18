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

$id = isset($_POST['id']) ? \basic\Basic::filterInt($_POST['id']) : ''; //文章ID

// 验证必要字段
if (empty($id)) {
    echo $jsonData->jsonData(400, '缺少必要参数，文章详情获取失败');
    exit;
}

$obj["data"] = array(); //返回数据

try {
    //要获取的字段值
    $news_field = processTableField('id,classid,title,url,keywords,description,thumbnail,enclosure,photo_album,content,add_time,view,seo_title,seo_keywords,seo_description', 'n');
    //sql查询语句
    $sql = "SELECT {$news_field},".
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
            "n.id=? ".
        "GROUP BY n.id";

    //准备SQL语句
    $stmt = $link->prepare($sql);
    if(!$stmt) throw new Exception('获取文章详情SQL语句准备失败：'.$link->error);
    //绑定参数
    if(!$stmt->bind_param('i',$id)) throw new Exception('绑定参数失败：'.$link->error);
    //执行查询
    if(!$stmt->execute()) throw new Exception('执行查询失败：'.$link->error);

    //获取查询结果
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row["classid"] = json_decode($row["classid"], true);
        $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
        $row["enclosure"] = getEnclosurePath(object_array(json_decode($row["enclosure"])));
        $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
        $row["url"] = processURL($row["url"]);
        // 处理合并后的字段数据
        $row["field"] = processFieldInfo($row["field_data"]);
        unset($row["field_data"]);

        $obj["data"] = $row;
    }

    $code = 200;
    $message = 'success';
} catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
