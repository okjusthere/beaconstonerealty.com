<?php
// 获取栏目表信息

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

// 判断后台用户权限
if (!my_power("column_list")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并过滤参数
$del = isset($_GET['del']) ? (int)$_GET['del'] : 0;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';

// 构建预处理SQL和参数
$where = '';
$params = [];
$types = 'i'; // is_delete 是整数

if (!empty($keywords)) {
    $where = " WHERE is_delete=? AND (title LIKE ? OR sub_title LIKE ? OR remarks LIKE ?)";
    $params = [$del, "%$keywords%", "%$keywords%", "%$keywords%"];
    $types .= 'sss'; // 添加3个字符串参数
} else {
    if ($del == 0) {
        $where = " WHERE is_delete=? AND parentid=0";
        $params = [$del];
    } else {
        $where = " WHERE is_delete=?";
        $params = [$del];
    }
}

// 主查询
$sql = "SELECT * FROM column_list{$where} ORDER BY is_show ASC, paixu DESC, id ASC";
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败: ' . $link->error;
} else {
    // 绑定参数
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // 执行查询
    if (!$stmt->execute()) {
        $code = 500;
        $message = '数据库查询执行失败: ' . $stmt->error;
    } else {
        $result = $stmt->get_result();
        if ($result) {
            $code = 200;
            $message = 'success';
            
            while ($row = $result->fetch_assoc()) {
                $row = processRowData($row);

                if ($row["parentid"] == 0 && empty($keywords) && $del == 0) {
                    $row["children"] = getChildCategories($row["id"], $del);
                }
                
                $obj["data"][] = $row;
            }
        }
    }
    $stmt->close();
}

/**
 * 处理行数据
 */
function processRowData($row) {
    $row["type"]=(string)$row["type"];
    $row["link_id"]=(string)$row["link_id"];
    $row['paixu'] = (int)$row['paixu'];
//    $row["is_show"] = ($row["is_show"] == '1');
    $row["thumbnail"] = json_decode($row["thumbnail"]);
    $row["banner"] = json_decode($row["banner"]);
    $row["allow_access"] = empty($row["allow_access"]) ? '' : json_decode($row["allow_access"]);
    $row["edit_title"] = false;
    $row["edit_sub_title"] = false;
    return $row;
}

/**
 * 递归获取子栏目（使用预处理）
 */
function getChildCategories($parentId, $delStatus) {
    global $link;
    $children = [];
    
    $sql = "SELECT * FROM column_list WHERE is_delete=? AND parentid=? ORDER BY is_show ASC, paixu DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $delStatus, $parentId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row = processRowData($row);
                $row["children"] = getChildCategories($row["id"], $delStatus);
                $children[] = $row;
            }
        }
        $stmt->close();
    }
    
    return $children;
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);