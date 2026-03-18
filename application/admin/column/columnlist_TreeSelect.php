<?php
// 获取栏目信息

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
$obj = ['data' => []]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 获取并过滤参数
$id_current = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 准备主查询（使用预处理）
$sql = 'SELECT * FROM column_list WHERE parentid = 0 AND is_delete = 0';
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败: ' . $link->error;
} else {
    // 执行查询
    if (!$stmt->execute()) {
        $code = 500;
        $message = '数据库查询执行失败: ' . $stmt->error;
    } else {
        $result = $stmt->get_result();
        if ($result) {
            $code = 200;
            $message = '查询成功！';
            
            while ($row = $result->fetch_assoc()) {
                $disabled = ($row["id"] == $id_current);
                $children = getChildCategories($row["id"], $id_current, $disabled);
                
                $obj["data"][] = [
                    "value" => (int)$row["id"],
                    "label" => $row["title"],
                    "disabled" => $disabled,
                    "children" => $children
                ];
            }
        }
    }
    $stmt->close();
}

/**
 * 递归获取子栏目（使用预处理）
 * 
 * @param int $parentId 父栏目ID
 * @param int $currentId 当前选中的栏目ID（需要禁用的）
 * @param bool $parentDisabled 父栏目是否已禁用
 * @return array 子栏目数组
 */
function getChildCategories($parentId, $currentId, $parentDisabled) {
    global $link;
    $children = [];
    
    $sql = 'SELECT * FROM column_list WHERE is_delete = 0 AND parentid = ?';
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $parentId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $disabled = $parentDisabled || ($row["id"] == $currentId);
                $grandChildren = getChildCategories($row["id"], $currentId, $disabled);
                
                $children[] = [
                    "value" => (int)$row["id"],
                    "label" => $row["title"],
                    "disabled" => $disabled,
                    "children" => $grandChildren
                ];
            }
        }
        $stmt->close();
    }
    
    return $children;
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);