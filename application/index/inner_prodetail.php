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

$id = isset($_POST['id']) ? \basic\Basic::filterInt($_POST['id']) : 0; //产品ID

// 验证必要字段
if (empty($id)) {
    echo $jsonData->jsonData(400, '缺少必要字段，产品详情获取失败');
    exit;
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $userpower = $_SESSION['u_power'] ?? 'no_login';//获取登录用户所属角色id
    $allow_sql = " AND (curr.allow_access IS NULL OR TRIM(curr.allow_access) = '' OR curr.allow_access LIKE ?)";
    $allow_sql_prev = " AND (prev_p.allow_access IS NULL OR TRIM(prev_p.allow_access) = '' OR prev_p.allow_access LIKE ?)";
    $allow_sql_next = " AND (next_p.allow_access IS NULL OR TRIM(next_p.allow_access) = '' OR next_p.allow_access LIKE ?)";
    $user_power = "%\"{$userpower}\"%";
    /*$prev_sql = "select id from (SELECT @rownum := @rownum + 1 AS rownum,product.id,title FROM ( SELECT @rownum := 0 ) r,product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as product_record where rownum=((select rownum from (SELECT @rownum2 := @rownum2 + 1 AS rownum,product.id FROM ( SELECT @rownum2 := 0 ) r,product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as product_record where id={$id})+1)"; // 查找上一个sql语句
    $next_sql = "select id from (SELECT @rownum := @rownum + 1 AS rownum,product.id,title FROM ( SELECT @rownum := 0 ) r,product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as product_record where rownum=((select rownum from (SELECT @rownum2 := @rownum2 + 1 AS rownum,product.id FROM ( SELECT @rownum2 := 0 ) r,product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as product_record where id={$id})-1)"; // 查找下一个sql语句
    $prev_sql_none="select id from product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc limit 0,1"; // 查找符合条件的最新的一个产品的sql语句
    $next_sql_none="select id from product where classid=(select classid from product where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top asc,paixu asc,id asc limit 0,1";*/ // 查找符合条件的第一个产品的sql语句

    $product_field=processTableField("id,classid,title,specifications,origin,price,keywords,description,thumbnail,enclosure,photo_album,content,add_time,view", "curr"); //要获取的产品字段
    //sql查询语句
    $sql = "SELECT ".
            "{$product_field},".
            "prev_article.id AS prev_id,".
            "prev_article.title AS prev_title,".
            "prev_rule.static_url AS prev_url,".
            "next_article.id AS next_id,".
            "next_article.title AS next_title,".
            "next_rule.static_url AS next_url,".
            "trr.static_url AS url,".
            "GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||') AS field_data ".
        "FROM ".
            "product curr ".
                "LEFT JOIN tb_rewrite_rules trr ON curr.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'product' AND fi.record_id = curr.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'product' AND fc.field_name = fi.field_name ".
                "LEFT JOIN product prev_article ON prev_article.id = (".
                    "SELECT prev_p.id FROM product prev_p ".
                    "WHERE prev_p.classid = curr.classid ".
                    "AND (".
                        "(prev_p.is_top > curr.is_top) ".
                            "OR (prev_p.is_top = curr.is_top AND prev_p.paixu > curr.paixu) ".
                            "OR (prev_p.is_top = curr.is_top AND prev_p.paixu = curr.paixu AND prev_p.id < curr.id) ".
                    "){$allow_sql_prev} ".
                    "ORDER BY prev_p.is_top DESC, prev_p.paixu DESC, prev_p.id DESC ".
                    "LIMIT 1".
                ") ".
                "LEFT JOIN product next_article ON next_article.id = (".
                    "SELECT next_p.id FROM product next_p ".
                    "WHERE next_p.classid = curr.classid ".
                    "AND ( ".
                        "(next_p.is_top < curr.is_top) ".
                            "OR (next_p.is_top = curr.is_top AND next_p.paixu < curr.paixu) ".
                            "OR (next_p.is_top = curr.is_top AND next_p.paixu = curr.paixu AND next_p.id > curr.id)".
                    "){$allow_sql_next} ".
                    "ORDER BY next_p.is_top ASC, next_p.paixu ASC, next_p.id ASC ".
                    "LIMIT 1".
                ") ".
                "LEFT JOIN tb_rewrite_rules prev_rule ON prev_rule.id = prev_article.rule_id ".
                "LEFT JOIN tb_rewrite_rules next_rule ON next_rule.id = next_article.rule_id ".
        "WHERE ".
            "curr.id=?{$allow_sql}".
        "GROUP BY {$product_field}, prev_id, prev_title, prev_url, next_id, next_title, next_url, url";

    //准备SQL语句
    $stmt = $link->prepare($sql);

    //绑定参数
    $stmt->bind_param('ssis',$user_power,$user_power,$id,$user_power);
    //执行语句
    if (!$stmt->execute()) throw new Exception('SQL语句执行失败：'.$link->error);

    //获取查询结果
    $result = $stmt->get_result();

    $code = 200;
    $message = 'success';

    while ($row = $result->fetch_assoc()) {
        $row["classid"] = json_decode($row["classid"], true);
        if (checkProductClassAllowState($row["classid"]) > 0) { //如果当前产品分类限制了访问权限，不返回数据
            echo $jsonData->jsonData($code, $message, $obj);
            exit();
        }
        $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
        $row["enclosure"] = getEnclosurePath(object_array(json_decode($row["enclosure"])));
        $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
        $row["url"] = processURL($row["url"]);
        $row["prev_url"] = processURL($row["prev_url"]);
        $row["next_url"] = processURL($row["next_url"]);
        // 处理合并后的自定义字段数据
        $row["field"] = processFieldInfo($row["field_data"]);
        unset($row["field_data"]);
        $row["attribute"] = getAttribute($row["id"]);

        $obj["data"] = $row;
    }
}catch (Exception $e) {
    $code = 100;
    $message = $e->getMessage();
} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

/*查询当前产品对应的产品属性信息
 * @param int $id：产品ID*/
function getAttribute($id): array
{
    $result = array();
    $sql = "select * from tb_product_attribute where product_id={$id}";
    $res = my_sql($sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row["id"] = (int)$row["id"];
            $row["product_id"] = (int)$row["product_id"];
            $row["attribute_value_id"] = object_array(json_decode($row["attribute_value_id"]));
            $row["attribute_value"] = object_array(json_decode($row["attribute_value"]));
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            $row["inventory"] = (int)$row["inventory"];
            $row["sort"] = (int)$row["sort"];
            $result[] = $row;
        }
    }
    return $result;
}

/*检查当前产品所属分类有没有限制访问权限
 *@param $classidary array 需要排查的产品分类id数组*/
function checkProductClassAllowState($classidary)
{
    $allow_state = 0;
    if (is_array($classidary)) {
        $classidary = implode(',', $classidary);
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
        $userpower = $_SESSION['u_power'] ?? 'no_login';
        $sql_check = "select id from product_class where id in({$classidary}) and allow_access is not null and length(trim(allow_access))>0 and allow_access not like '%\"{$userpower}\"%'";
        $res_check = my_sql($sql_check);
        if ($res_check) {
            $allow_state = mysqli_num_rows($res_check);
        }
    }
    return $allow_state;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
