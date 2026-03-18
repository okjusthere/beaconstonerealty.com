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

if ($id > 0) {
    //sql查询语句
    $sql = "select id,classid,title,keywords,description,thumbnail,enclosure,photo_album,add_time,seo_title,seo_keywords,seo_description from product where id={$id}";
    //获取查询结果集
    $res = my_sql($sql);

    if ($res) {
        $code = 200;
        $message = '查询成功！';
        $obj["data"] = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row["classid"] = object_array(json_decode($row["classid"]));
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            $row["field"] = getFieldInfo('product', $row["id"]);
            $obj["data"] = $row;
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
