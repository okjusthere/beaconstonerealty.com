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

$id = isset($_POST['id']) ? \basic\Basic::filterInt($_POST['id']) : 0; //文章ID

if ($id > 0) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $userpower = $_SESSION['u_power'] ?? 'no_login'; //获取登录用户所属角色id
    $allow_sql = " AND IF(isnull(n.allow_access) = 1 || length(trim(n.allow_access)) = 0,n.id>0,n.allow_access LIKE ?) ";

    $news_field = processTableField("id,classid,title,keywords,description,thumbnail,photo_album,content,add_time", "n"); //要获取的文章字段
    //sql查询语句
    $sql = "SELECT ".
            "{$news_field}".
            ",trr.static_url AS url".
            ",(SELECT GROUP_CONCAT(DISTINCT cl.id) FROM column_list cl WHERE cl.type = 2 AND cl.link_id = n.id) as c_ids".
            ",GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||') as field_data ".
        "FROM ".
            "news n ".
                "INNER JOIN tb_rewrite_rules trr ON n.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'news' AND fi.record_id = n.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'news' AND fc.field_name = fi.field_name ".
        "WHERE ".
            "n.id = ?{$allow_sql}".
        "GROUP BY n.id";

    //准备SQL语句
    $stmt = $link->prepare($sql);
    $allow_param = "%\"{$userpower}\"%"; //$allow_sql语句里面对应的参数
    $stmt->bind_param('is', $id, $allow_param); //绑定参数
    //执行SQL语句
    if (!$stmt->execute()) {
        $stmt->close(); //关闭语句
        $code = 100;  //响应码
        $message = '查询失败：' . $link->error;  //响应信息
        echo $jsonData->jsonData($code, $message, $obj);
        exit;
    }

    //获取查询结果
    $result = $stmt->get_result();

    $code = 200;
    $message = 'success';
    $obj["data"] = array();
    while ($row = $result->fetch_assoc()) {
        $row["thumbnail"] = getThumbnailPath(json_decode($row["thumbnail"], true));
        $row["photo_album"] = getPhotoAlbumPath(json_decode($row["photo_album"], true));
        $row["url"] = processURL($row["url"]);
        // 处理合并后的自定义字段数据
        $row["field"] = processFieldInfo($row["field_data"]);
        unset($row["field_data"]);
        //处理选中的菜单栏ID
        $row["c_id"] = empty($row["c_ids"]) ? [] : explode(',', $row["c_ids"]);
        unset($row["c_ids"]);

        $obj["data"] = $row;
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
