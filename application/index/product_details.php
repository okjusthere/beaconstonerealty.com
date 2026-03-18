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

//产品详情请求参数
$param = isset($_POST['param']) ? \basic\Basic::filterStr($_POST['param']) : '';

// 验证必要字段
if (empty($param)) {
    echo $jsonData->jsonData(400, '缺少必要参数，产品详情获取失败');
    exit;
}

$paramAry = json_decode($param, true); //将参数从string转换成数组

$obj["data"] = array(); //返回数据

try {
    $info_ary = array();
    $id_ary = []; //请求的ID数组
    $params = []; //请求参数集合
    $types = ""; //请求参数类型集合
    foreach ($paramAry as $value) {
        $info_ary[$value["id"]]["show_type"] = $value["type"];
        $types .= 'i';
        $params[] = $value["id"];
        $id_ary[] = $value["id"];
    }
    $placeholders = str_repeat('?,', count($id_ary) - 1) . '?'; //SQL语句中的占位符

    //要获取的字段值
    $product_field = processTableField('id,classid,title,keywords,description,thumbnail,enclosure,photo_album,content,add_time,seo_title,seo_keywords,seo_description', 'p');
    // 构建FIELD排序部分
    $field_order = "FIELD(p.id, " . implode(',', array_fill(0, count($id_ary), '?')) . ")";
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
            "p.id IN ({$placeholders}) ".
        "GROUP BY {$product_field} ".
        "ORDER BY {$field_order}";
    $types .= str_repeat('i', count($id_ary)); //合并参数类型
    $params = array_merge($params, $id_ary); //合并请求参数

    //准备SQL语句
    $stmt = $link->prepare($sql);
    if(!$stmt) throw new Exception('获取文章详情SQL语句准备失败：'.$link->error);
    //绑定参数
    if(!$stmt->bind_param($types, ...$params)) throw new Exception('绑定参数失败：'.$link->error);
    //执行查询
    if(!$stmt->execute()) throw new Exception('执行查询失败：'.$link->error);

    //获取查询结果
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row["classid"] = json_decode($row["classid"], true);
        $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
        $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
        $row["url"] = processURL($row["url"]);
        // 处理合并后的字段数据
        $row["field"] = processFieldInfo($row["field_data"]);
        unset($row["field_data"]);
        $id = $row["id"];
        $ary = array();
        if (isset($info_ary[$id])) {
            // 合并匹配的数据
            $obj["data"][] = array_merge($info_ary[$id], $row);
        }
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
