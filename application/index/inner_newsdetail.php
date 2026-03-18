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

$obj["data"] = array();
if ($id > 0) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $userpower = $_SESSION['u_power'] ?? 'no_login'; //获取登录用户所属角色id
    $allow_sql = " AND IF(isnull(curr.allow_access)=1||length(trim(curr.allow_access))=0,curr.id>0,curr.allow_access LIKE ?)";
    $allow_sql_prev = " AND IF(isnull(prev_n.allow_access)=1||length(trim(prev_n.allow_access))=0,prev_n.id>0,prev_n.allow_access LIKE ?)";
    $allow_sql_next = " AND IF(isnull(next_n.allow_access)=1||length(trim(next_n.allow_access))=0,next_n.id>0,next_n.allow_access LIKE ?)";
    $user_power = "%\"{$userpower}\"%";
    /*$prev_sql = "select id from (SELECT @rownum := @rownum + 1 AS rownum,news.id,title FROM ( SELECT @rownum := 0 ) r,news where classid=(select classid from news where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as news_record where rownum=((select rownum from (SELECT @rownum2 := @rownum2 + 1 AS rownum,news.id FROM ( SELECT @rownum2 := 0 ) r,news where classid=(select classid from news where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as news_record where id={$id})+1)"; // 查找上一个sql语句
    $next_sql = "select id from (SELECT @rownum := @rownum + 1 AS rownum,news.id,title FROM ( SELECT @rownum := 0 ) r,news where classid=(select classid from news where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as news_record where rownum=((select rownum from (SELECT @rownum2 := @rownum2 + 1 AS rownum,news.id FROM ( SELECT @rownum2 := 0 ) r,news where classid=(select classid from news where id={$id}) and is_delete=0 and is_show=1{$allow_sql} ORDER BY is_top desc,paixu desc,id desc) as news_record where id={$id})-1)"; // 查找下一个sql语句
    $news_field = processTableField("id,classid,title,url,keywords,description,thumbnail,enclosure,photo_album,content,add_time,view","n"); //要获取的文章字段
    //sql查询语句
    $sql = "SELECT {$news_field},({$prev_sql}) AS prev_id,({$next_sql}) AS next_id,CASE WHEN n.url IS NULL OR n.url='' THEN trr.static_url ELSE n.url END AS url FROM news n INNER JOIN tb_rewrite_rules trr ON n.rule_id = trr.id WHERE n.id={$id}{$allow_sql}";*/
    $news_field = processTableField("id,classid,title,url,keywords,description,thumbnail,enclosure,photo_album,content,add_time,view","curr"); //要获取的当前文章字段
    $sql = "SELECT ".
            "{$news_field}".
            ",CASE WHEN curr.url IS NULL OR curr.url='' THEN trr.static_url ELSE curr.url END AS url,".
            "prev_article.id AS prev_id,".
            "prev_article.title AS prev_title,".
            "CASE ".
                "WHEN IFNULL(prev_article.url, '') != '' THEN prev_article.url ".
                "ELSE prev_rule.static_url ".
                "END AS prev_url,".
            "next_article.id AS next_id,".
            "next_article.title AS next_title,".
            "CASE ".
                "WHEN IFNULL(next_article.url, '') != '' THEN next_article.url ".
                "ELSE next_rule.static_url ".
                "END AS next_url,".
            "GROUP_CONCAT(CONCAT_WS('::', fi.field_name, fi.field_content, fc.field_type) SEPARATOR '|||')
             as field_data ".
        "FROM ".
            "news curr ".
                "LEFT JOIN tb_rewrite_rules trr ON curr.rule_id = trr.id ".
                "LEFT JOIN field_info fi ON fi.table_name = 'news' AND fi.record_id = curr.id ".
                "LEFT JOIN field_custom fc ON fc.table_name = 'news' AND fc.field_name = fi.field_name ".
                "LEFT JOIN news prev_article ON prev_article.id = (".
                "SELECT prev_n.id FROM news prev_n ".
                "WHERE prev_n.classid = curr.classid ".
                  "AND (".
                "(prev_n.is_top > curr.is_top) ".
                        "OR (prev_n.is_top = curr.is_top AND prev_n.paixu > curr.paixu) ".
                        "OR (prev_n.is_top = curr.is_top AND prev_n.paixu = curr.paixu AND prev_n.id < curr.id) ".
                    "){$allow_sql_prev} ".
                "ORDER BY prev_n.is_top DESC, prev_n.paixu DESC, prev_n.id DESC ".
                "LIMIT 1".
            ") ".
                "LEFT JOIN news next_article ON next_article.id = (".
            "SELECT next_n.id FROM news next_n ".
                "WHERE next_n.classid = curr.classid ".
                  "AND ( ".
                        "(next_n.is_top < curr.is_top) ".
                        "OR (next_n.is_top = curr.is_top AND next_n.paixu < curr.paixu) ".
                        "OR (next_n.is_top = curr.is_top AND next_n.paixu = curr.paixu AND next_n.id > curr.id)".
                    "){$allow_sql_next} ".
                "ORDER BY next_n.is_top ASC, next_n.paixu ASC, next_n.id ASC ".
                "LIMIT 1".
            ") ".
                "LEFT JOIN tb_rewrite_rules prev_rule ON prev_rule.id = prev_article.rule_id ".
                "LEFT JOIN tb_rewrite_rules next_rule ON next_rule.id = next_article.rule_id ".
        "WHERE ".
                "curr.id = ?{$allow_sql}".
        "GROUP BY {$news_field}, prev_id, prev_title, prev_url, next_id, next_title, next_url";

    //准备SQL语句
    $stmt = $link->prepare($sql);
    //绑定参数
    $stmt->bind_param('ssis',$user_power,$user_power,$id,$user_power);
    //执行语句
    if ($stmt->execute()) {
        //获取查询结果
        $result = $stmt->get_result();
        $code = 200;
        $message = 'success';
        while ($row = $result->fetch_assoc()) {
            $row["classid"] = json_decode($row["classid"],true);
            if (checkNewsClassAllowState($row["classid"]) > 0) { //如果当前文章分类限制了访问权限，不返回数据
                //关闭语句
                $stmt->close();
                echo $jsonData->jsonData($code, $message, $obj);
                exit;
            }
            $row["thumbnail"] = getThumbnailPath(json_decode($row["thumbnail"],true));
            $row["enclosure"] = getEnclosurePath(json_decode($row["enclosure"],true));
            $row["photo_album"] = getPhotoAlbumPath(json_decode($row["photo_album"],true));
            $row["url"] = processURL($row["url"]);
            $row["prev_url"] = processURL($row["prev_url"]);
            $row["next_url"] = processURL($row["next_url"]);
            // 处理合并后的自定义字段数据
            // $row["field"] = processFieldInfo($row["field_data"]);
            $row["field"] = getFieldInfo('news',$row["id"]);
            unset($row["field_data"]);

            $obj["data"] = $row;
        }
    }
    //关闭语句
    $stmt->close();
}

/*检查当前文章所属分类有没有限制访问权限
 *@param $classidary array 需要排查的文章分类id数组*/
function checkNewsClassAllowState($classidary)
{
    $allow_state = 0;
    if (is_array($classidary)) {
        $classidary = implode(',', $classidary);
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
        $userpower = $_SESSION['u_power'] ?? 'no_login';
        $sql_check = "select id from news_class where id in({$classidary}) and allow_access is not null and length(trim(allow_access))>0 and allow_access not like '%\"{$userpower}\"%'";
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
