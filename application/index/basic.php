<?php
//获取网站开关
function getWebControl(mysqli $link)
{
    $result = array(); //返回结果
    //sql查询语句
    $sql = 'select state,tips from web_control where id=1';
    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        $result = $res->fetch_assoc();
    }
    // 关闭语句
    $stmt->close();
    return $result;
}

//网站SEO信息
function getWebSeoInfo(mysqli $link)
{
    $result = array(); //返回结果
    //sql查询语句
    $sql = 'select seo_title,seo_keywords,seo_description from web_seo where id=1';
    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        $result = $res->fetch_assoc();
    }
    // 关闭语句
    $stmt->close();
    return $result;
}

//网站信息
function getWebInfo(mysqli $link)
{
    $result = array(); //返回结果
    //sql查询语句
    $sql = 'select company,address,phone,mobile,email,fax,contact,qq,wechat,whatsapp,zip,icp,icp_police,weburl,map from web_info where id=1';
    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        $result = $res->fetch_assoc();
    }
    // 关闭语句
    $stmt->close();
    return $result;
}

//嵌入代码
function getWebCode(mysqli $link): array
{
    $result = array(); //返回结果
    //sql查询语句
    $sql = "select state,code from code where id=1 and state=2";

    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $row["code"] = htmlspecialchars_decode($row["code"]);
            $result[] = $row;
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

//图片管理信息
function getPicInfo(mysqli $link): array
{
    $result = array(); //返回结果

    //sql查询语句
    $sql = "select id,classid,path,name,url,remarks from pic_info order by paixu desc,id desc";

    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $row["path"] = getThumbnailPath(object_array(json_decode($row["path"])));
            $result[] = $row;
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

/* 导航菜单信息
 * @param mysqli $link 数据库链接信息
 * @param int $parent_id 父级栏目ID
 */
function getMenuInfo(mysqli $link, $parent_id = 0): array
{
    $result = array(); //返回结果

    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); //开启session
    $user_power = $_SESSION['u_power'] ?? 'no_login'; //获取当前用户对应权限的ID

    $menu_field = processTableField("id,parentid,type,link_id,title,sub_title,url,remarks,thumbnail,banner,is_show,allow_access,seo_title,seo_keywords,seo_description", "c"); //要获取的导航菜单字段
    $menu_field .= ",CASE" .
        " WHEN c.type = 1 THEN c.url" .
        " WHEN (c.type = 2 or c.type = 3) THEN trr1.static_url" .
        " WHEN c.type = 4 THEN trr2.static_url" .
        " WHEN c.type = 5 THEN trr3.static_url" .
        " WHEN c.type = 6 THEN trr4.static_url" .
        " END AS url";
    $join_sql = " LEFT JOIN news n ON (c.type = 2 or c.type = 3) AND c.link_id = n.id" .
        " LEFT JOIN news_class nc ON c.type = 4 AND c.link_id = nc.id" .
        " LEFT JOIN product p ON c.type = 5 AND c.link_id = p.id" .
        " LEFT JOIN product_class pc ON c.type = 6 AND c.link_id = pc.id" .
        " LEFT JOIN tb_rewrite_rules trr1 ON n.rule_id = trr1.id" .
        " LEFT JOIN tb_rewrite_rules trr2 ON nc.rule_id = trr2.id" .
        " LEFT JOIN tb_rewrite_rules trr3 ON p.rule_id = trr3.id" .
        " LEFT JOIN tb_rewrite_rules trr4 ON pc.rule_id = trr4.id";

    //sql查询语句
    $sql = "SELECT {$menu_field} FROM column_list c{$join_sql} WHERE c.is_delete=0 AND c.parentid=? ORDER BY c.is_show ASC,c.paixu DESC,c.id ASC";
    $stmt = $link->prepare($sql); //准备SQL语句
    $stmt->bind_param('i', $parent_id); //绑定参数
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                //将是否显示状态由数字转换成bool值
                $row["is_show"] = ($row["is_show"] == '1');
                $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
                $row["banner"] = getPhotoAlbumPath(object_array(json_decode($row["banner"])));
                $row["allow_access"] = empty($row["allow_access"]) ? array() : object_array(json_decode($row["allow_access"]));
                if (!empty($row["allow_access"]) && !in_array($user_power, $row["allow_access"])) {
                    $row["url"] = "nopermission"; //没有访问权限的用户，参数url设置为nopermission
                }
                $row["url"] = processURL($row["url"]);
                $row["children"] = getMenuInfo($link, $row["id"]); //获得子栏目
                $result[] = $row;
            }
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

/* 文章分类
 * @param mysqli $link 数据库链接信息
 * @param int $parent_id 父级分类ID
 */
function getNewsClassInfo(mysqli $link, $parent_id = 0): array
{
    $result = array(); //返回结果

    $news_class_field = processTableField("id,parentid,title,description,thumbnail,banner,content,show_type,seo_title,seo_keywords,seo_description", 'nc'); //要获取的文章分类字段处理
    //sql查询语句
    $sql = "SELECT ".
            "{$news_class_field}".
            ",trr.static_url as url".
            ",GROUP_CONCAT(cl.id) as c_ids ".
        "FROM ".
            "news_class nc ".
                "INNER JOIN tb_rewrite_rules trr ON nc.rule_id = trr.id ".
                "LEFT JOIN column_list cl ON cl.type = 4 AND cl.link_id = nc.id ".
        "WHERE ".
            "nc.is_show=1 ".
            "AND nc.is_delete=0 ".
            "AND nc.parentid=? ".
        "GROUP BY nc.id, nc.paixu ".
        "ORDER BY ".
            "nc.paixu DESC,nc.id ASC";
    $stmt = $link->prepare($sql); //准备SQL语句
    $stmt->bind_param('i', $parent_id); //绑定参数
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
                $row["banner"] = getPhotoAlbumPath(object_array(json_decode($row["banner"])));
                $row["url"] = processURL($row["url"]);
                $row["c_id"] = empty($row["c_ids"]) ? [] : explode(',', $row["c_ids"]);
                $row["children"] = getNewsClassInfo($link, $row["id"]);
                unset($row["c_ids"]);
                $result[] = $row;
            }
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

/* 产品分类
 * @param mysqli $link 数据库链接信息
 * @param int $parent_id 父级分类ID
 */
function getProductClassInfo(mysqli $link, $parent_id = 0): array
{
    $result = array(); //返回结果
    $product_class_field = processTableField("id,parentid,title,description,thumbnail,banner,content,show_type,seo_title,seo_keywords,seo_description", "pc"); //要获取的产品分类字段
    //sql查询语句
    $sql = "SELECT ".
            "{$product_class_field}".
            ",trr.static_url as url".
            ",GROUP_CONCAT(cl.id) as c_ids ".
        "FROM ".
            "product_class pc ".
                "INNER JOIN tb_rewrite_rules trr ON pc.rule_id = trr.id ".
                "LEFT JOIN column_list cl ON cl.type = 6 AND cl.link_id = pc.id ".
        "WHERE ".
            "pc.is_delete=0 ".
            "AND pc.is_show=1 ".
            "AND pc.parentid=? ".
        "GROUP BY pc.id, pc.paixu ".
        "ORDER BY ".
            "pc.paixu DESC,pc.id ASC";
    $stmt = $link->prepare($sql); //准备SQL语句
    $stmt->bind_param('i', $parent_id); //绑定参数
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $row["banner"] = getPhotoAlbumPath(object_array(json_decode($row["banner"])));
            $row["url"] = processURL($row["url"]);
            $row["c_id"] = empty($row["c_ids"]) ? [] : explode(',', $row["c_ids"]);
            $row["children"] = getProductClassInfo($link, $row["id"]);
            unset($row["c_ids"]);
            $result[] = $row;
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

//获取产品分类子分类
/*function getProductClassChild($id, $product_class_field): array
{
    $obj_child = array();

    if (\basic\Basic::filterInt($id) > 0) {
        $res_child = my_sql("select {$product_class_field} from product_class where is_show=1 and is_delete=0 and parentid={$id} order by paixu desc,id asc");
        if ($res_child && mysqli_num_rows($res_child) > 0) {
            while ($row = mysqli_fetch_assoc($res_child)) {
                $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
                $row["banner"] = getPhotoAlbumPath(object_array(json_decode($row["banner"])));
                $row["show_type"] = (int)$row["show_type"];
                $row["children"] = getProductClassChild($row["id"], $product_class_field);

                $obj_child[] = $row;
            }
        }
    }
    return $obj_child;
}*/

//右侧客服信息
function getCustomerService(): array
{
    $data = file_get_contents('../admin/config/customerservice.json');
    $data = json_decode($data, true);

    $result = array(); //返回结果
    foreach ($data as $k => $val) {
        if ($val["key"] == "wechat") {
            $val["value"] = getThumbnailPath($val["value"]);
        }
        $result[] = $val;
    }
    return $result;
}

//友情链接
function getLinksInfo(mysqli $link): array
{
    $result = array(); //返回结果
    //sql查询语句
    $sql = "select id,classid,title,url,thumbnail from tb_links where is_show=1 order by is_top desc,sort desc,id desc";

    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $row["classid"] = object_array(json_decode($row["classid"]));
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));

            $result[] = $row;
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}

//友情链接分类
function getLinksClassInfo(mysqli $link): array
{
    $result = array(); //返回结果

    //sql查询语句
    $sql = "select id,parentid,title,thumbnail from tb_links_class where is_show=1 order by sort desc,id desc";
    $stmt = $link->prepare($sql); //准备SQL语句
    //执行SQL语句
    if ($stmt->execute()) {
        $res = $stmt->get_result(); //获取查询结果
        while ($row = $res->fetch_assoc()) {
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));

            $result[] = $row;
        }
    }
    // 关闭语句
    $stmt->close();

    return $result;
}