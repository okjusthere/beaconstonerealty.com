<?php
// 获取产品表信息
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

$code = 500;  // 默认响应码
$message = '未响应，请重试！';  // 默认响应信息
$obj = array(); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("product_list")) {
    echo $jsonData->jsonData(403, '您没有管理该页面的权限！', []);
    exit;
}

// 获取并验证参数
$type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1;
$pagesize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize']) : 10;
$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

// 验证type_id
if ($type_id <= 0) {
    echo $jsonData->jsonData(400, '无效的表单分类ID', []);
    exit;
}

// 准备基础SQL和参数
$sql = "SELECT * FROM tb_form WHERE type_id = ?";
$params = array($type_id);
$param_types = "i";

// 添加关键词搜索条件
if (!empty($keywords)) {
    $sql .= " AND (content LIKE ? OR remarks LIKE ?)";
    $search_param = "%{$keywords}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

$sql .= " ORDER BY id DESC";

// 获取总记录数
$stmt_total = $link->prepare($sql);
if (!$stmt_total) {
    echo $jsonData->jsonData(500, '查询准备失败', []);
    exit;
}

// 绑定参数
$bind_names[] = $param_types;
for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array(array($stmt_total, 'bind_param'), $bind_names);

if (!$stmt_total->execute()) {
    $stmt_total->close();
    echo $jsonData->jsonData(500, '查询执行失败', []);
    exit;
}

$result_total = $stmt_total->get_result();
$total = $result_total->num_rows;
$stmt_total->close();

// 获取分页数据
$start = ($currentpage - 1) * $pagesize;
$sql_page = $sql . " LIMIT ?, ?";
$params[] = $start;
$params[] = $pagesize;
$param_types .= "ii";

$stmt_page = $link->prepare($sql_page);
if (!$stmt_page) {
    echo $jsonData->jsonData(500, '分页查询准备失败', []);
    exit;
}

// 重新绑定参数
$bind_names_page[] = $param_types;
for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind_page' . $i;
    $$bind_name = $params[$i];
    $bind_names_page[] = &$$bind_name;
}

call_user_func_array(array($stmt_page, 'bind_param'), $bind_names_page);

if (!$stmt_page->execute()) {
    $stmt_page->close();
    echo $jsonData->jsonData(500, '分页查询执行失败', []);
    exit;
}

$result_page = $stmt_page->get_result();

// 获取字段信息
$field_info = array();
$stmt_type = $link->prepare("SELECT field FROM tb_form_type WHERE id = ?");
if ($stmt_type) {
    $stmt_type->bind_param("i", $type_id);
    if ($stmt_type->execute()) {
        $result_type = $stmt_type->get_result();
        if ($row = $result_type->fetch_assoc()) {
            $field_info = json_decode($row['field'], true);
        }
    }
    $stmt_type->close();
}

// 处理结果数据
$code = 200;
$message = '查询成功！';
$obj["total"] = $total;
$obj["data"] = array();

while ($row = $result_page->fetch_assoc()) {
    $row["content"] = json_decode($row["content"], true);
    if (is_array($field_info) && count($field_info) > 0) {
        foreach ($field_info as $value) {
            $row[$value['field_name']] = getFieldContent($value['field_name'], $row["content"]);
        }
    }
    $obj["data"][] = $row;
}

$stmt_page->close();

/**
 * 获取表单对应字段的值
 * @param string $field_name 字段（英文）名称
 * @param array $field_info 字段信息
 * @return string
 */
function getFieldContent($field_name, $field_info)
{
    foreach ($field_info as $value) {
        if ($value['field_name'] === $field_name) {
            $type = $value['field_type'];
            $content = $value['field_content'] ?? '';
            
            switch ($type) {
                case 3: // 多选类型
                    return !empty($content) ? implode(',', $content) : '';
                case 5: // 文件类型
                    return !empty($content) 
                        ? "<a href='{$content}' target='_blank' style='color: blue;'>查看</a>" 
                        : "<span style='color: #d9d9d9'>未上传</span>";
                default:
                    return $content;
            }
        }
    }
    return "";
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);