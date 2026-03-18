<?php
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

$proid = isset($_GET['proid']) ? (int)$_GET['proid'] : 0; // 产品记录id，安全过滤
$attr_id_str = ''; // 被选中的属性值ID集合

if ($proid > 0) {
    $attr_id_str = getAttributeId($proid);
    if (!empty($attr_id_str)) {
        $attr_id_str = rtrim($attr_id_str, ',');
    }
}

// 使用预处理语句查询顶级分类
$sql = "SELECT * FROM tb_product_attribute_class WHERE parentid=0 ORDER BY is_show DESC, is_top DESC, sort DESC, id ASC";
$stmt = $link->prepare($sql);

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $code = 200;
    $message = '查询成功！';

    while ($row = $result->fetch_assoc()) {
        $row["sort"] = (int)$row["sort"];
        $row["is_show"] = $row["is_show"] != '1';
        $row["is_top"] = $row["is_top"] != '1';
        $row["children"] = getChild($row["id"], $attr_id_str);
        $row["attribute"] = getAttributeValue($row["id"]);
        $row["selected"] = !empty($attr_id_str) ? getAttributeValueSelected($row["id"], $attr_id_str) : [];
        
        $obj["data"][] = $row;
    }
    
    $stmt->close();
} else {
    $code = 100;
    $message = '查询失败';
}

// 获取子分类
function getChild($id, $attr_id_str): array {
    global $link;
    $obj_child = array();

    $sql = "SELECT * FROM tb_product_attribute_class WHERE parentid=? ORDER BY is_show DESC, is_top DESC, sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt && $stmt->bind_param('i', $id) && $stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row["sort"] = (int)$row["sort"];
            $row["is_show"] = $row["is_show"] != '1';
            $row["is_top"] = $row["is_top"] != '1';
            $row["children"] = getChild($row["id"], $attr_id_str);
            $row["attribute"] = getAttributeValue($row["id"]);
            $row["selected"] = !empty($attr_id_str) ? getAttributeValueSelected($row["id"], $attr_id_str) : [];
            $obj_child[] = $row;
        }
        
        $stmt->close();
    }
    
    return $obj_child;
}

/* 获取产品属性分类对应的属性值 */
function getAttributeValue($id): array {
    global $link;
    $data = array();

    $sql = "SELECT * FROM tb_product_attribute_value WHERE attribute_class=? ORDER BY sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt && $stmt->bind_param('i', $id) && $stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                "id" => (int)$row["id"],
                "value" => $row["value"],
                "sort" => (int)$row["sort"]
            ];
        }
        
        $stmt->close();
    }
    
    return $data;
}

/* 获取当前有哪些属性值被选中 */
function getAttributeValueSelected($classid, $attr_id_str): array {
    global $link;
    $result = array();
    
    // 将逗号分隔的字符串转换为数组并过滤
    $attr_ids = array_filter(explode(',', $attr_id_str), 'is_numeric');
    if (empty($attr_ids)) return $result;
    
    $placeholders = implode(',', array_fill(0, count($attr_ids), '?'));
    $sql = "SELECT * FROM tb_product_attribute_value WHERE id IN ($placeholders) AND attribute_class=? ORDER BY sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        // 动态绑定参数
        $types = str_repeat('i', count($attr_ids)) . 'i';
        $params = array_merge($attr_ids, [$classid]);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $result[] = $row["id"] . "|" . $row["value"];
            }
        }
        
        $stmt->close();
    }
    
    return $result;
}

/* 获取当前产品属性值ID */
function getAttributeId($proid): string {
    global $link;
    $attribute_value_id_str = "";

    $sql = "SELECT attribute_value_id FROM tb_product_attribute WHERE product_id=?";
    $stmt = $link->prepare($sql);
    
    if ($stmt && $stmt->bind_param('i', $proid) && $stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $ids = json_decode($row["attribute_value_id"]);
            if (is_array($ids)) {
                $attribute_value_id_str .= implode(',', $ids) . ',';
            }
        }
        
        $stmt->close();
    }
    
    return $attribute_value_id_str;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);