<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
/*if (!my_power("proclass_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}*/

$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : ''; //搜索关键词

// 主查询（使用预处理）
$sql = "SELECT * FROM tb_product_attribute_class WHERE ";
$params = array();
$types = '';

if (!empty($keywords)) {
    $sql .= "title LIKE ?";
    $params[] = "%{$keywords}%";
    $types .= 's';
} else {
    $sql .= "parentid = ?";
    $params[] = 0;
    $types .= 'i';
}

$sql .= " ORDER BY is_show DESC, is_top DESC, sort DESC, id ASC";

$stmt = $link->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $code = 200;
        $message = '查询成功！';

        while ($row = $result->fetch_assoc()) {
            $row["sort"] = (int)$row["sort"];
            $row["is_show"] = ($row["is_show"] != '1');
            $row["is_top"] = ($row["is_top"] != '1');
            
            if ($row["parentid"] == 0 && empty($keywords)) {
                $row["children"] = getChild($row["id"]);
            }
            
            $row["attribute"] = getAttributeValue($row["id"]);
            $row["edit_title"] = false;
            $obj["data"][] = $row;
        }
        
        $result->free();
    }
    $stmt->close();
}

//获取子分类（使用预处理）
function getChild($id)
{
    global $link;
    $obj_child = array();

    $sql = "SELECT * FROM tb_product_attribute_class WHERE parentid = ? ORDER BY is_show DESC, is_top DESC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $row["sort"] = (int)$row["sort"];
                $row["is_show"] = ($row["is_show"] != '1');
                $row["is_top"] = ($row["is_top"] != '1');
                $row["children"] = getChild($row["id"]);
                $row["attribute"] = getAttributeValue($row["id"]);
                $row["edit_title"] = false;
                $obj_child[] = $row;
            }
            
            $result->free();
        }
        $stmt->close();
    }
    
    return $obj_child;
}

/*获取产品属性分类对应的属性值（使用预处理）
 * @param int $id 产品属性分类id*/
function getAttributeValue($id)
{
    global $link;
    $data = array();

    $sql = "SELECT * FROM tb_product_attribute_value WHERE attribute_class = ? ORDER BY sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    "id" => (int)$row["id"],
                    "value" => $row["value"],
                    "sort" => (int)$row["sort"]
                ];
            }
            
            $result->free();
        }
        $stmt->close();
    }
    
    return $data;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);