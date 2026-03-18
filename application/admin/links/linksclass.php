<?php
// 获取文章分类表信息
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

// 获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  // 响应码
$message = '未响应，请重试！';  // 响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("linksclass_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : ''; // 搜索关键词

// 查询顶级分类
if (empty($keywords)) {
    $sql = "SELECT * FROM tb_links_class WHERE parentid = 0 ORDER BY is_show ASC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);
} else {
    $sql = "SELECT * FROM tb_links_class WHERE parentid = 0 AND title LIKE ? ORDER BY is_show ASC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    $searchTerm = "%{$keywords}%";
    $stmt->bind_param("s", $searchTerm);
}

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $stmt->close();
    
    $code = 200;
    $message = '查询成功！';
    
    while ($row = $result->fetch_assoc()) {
        $row["sort"] = (int)$row["sort"];
        $row["children"] = getChild($row["id"]);
        $row["thumbnail"] = json_decode($row["thumbnail"]);
        $row["edit_show"] = true;
        
        $obj["data"][] = $row;
    }
} else {
    $code = 500;
    $message = '数据库查询失败';
    if ($stmt) {
        $message .= ': ' . $stmt->error;
    }
}

/**
 * 获取子分类
 * @param int $id 父分类ID
 * @return array 子分类数组
 */
function getChild($id)
{
    global $link;
    $obj_child = array();
    
    $sql = "SELECT * FROM tb_links_class WHERE parentid = ? ORDER BY is_show ASC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $row["sort"] = (int)$row["sort"];
                    $row["children"] = getChild($row["id"]);
                    $row["thumbnail"] = json_decode($row["thumbnail"]);
                    $row["edit_show"] = true;
                    
                    $obj_child[] = $row;
                }
            }
            $result->free();
        }
        $stmt->close();
    }
    
    return $obj_child;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);