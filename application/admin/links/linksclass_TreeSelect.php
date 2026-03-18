<?php
// 获取文章分类信息
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
$obj = array('data' => array()); // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$linkslist = isset($_GET['linkslist']) ? (int)$_GET['linkslist'] : 0; // 是否是新闻列表页在引用
$id_current = isset($_GET['id']) ? (int)$_GET['id'] : 0; // 禁止选中的分类ID

// 查询顶级分类
$sql = 'SELECT * FROM tb_links_class WHERE parentid = 0 ORDER BY sort DESC, id ASC';
$stmt = $link->prepare($sql);

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $code = 200;
    $message = '查询成功！';
    
    while ($row = $result->fetch_assoc()) {
        $disabled = ($row['id'] == $id_current);
        
        // 获取子分类
        $children = getChild($row['id'], $id_current, $disabled, $linkslist);
        
        // 如果需要显示文章数量
        $title = $row['title'];
        if ($linkslist == 1) {
            $count = getLinksNumber($row['id']);
            $title .= "({$count})";
        }
        
        $obj['data'][] = [
            'value' => $row['id'],
            'label' => $title,
            'disabled' => $disabled,
            'children' => $children
        ];
    }
    
    $stmt->close();
} else {
    $code = 500;
    $message = '查询失败: ' . ($stmt ? $stmt->error : $link->error);
}

/**
 * 获取子分类
 * @param int $id 父级分类ID
 * @param int $id_cur 禁止选中的分类ID
 * @param bool $child_state 父级分类是否选中
 * @param int $linkslist 是否显示文章数量
 * @return array 子分类数组
 */
function getChild($id, $id_cur, $child_state, $linkslist)
{
    global $link;
    $obj_child = array();
    
    $sql = "SELECT * FROM tb_links_class WHERE parentid = ? ORDER BY sort DESC, id ASC";
    $stmt = $link->prepare($sql);
    
    if ($stmt && $stmt->bind_param('i', $id) && $stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $disabled = $child_state || ($row['id'] == $id_cur);
            $children = getChild($row['id'], $id_cur, $disabled, $linkslist);
            
            $title = $row['title'];
            if ($linkslist == 1) {
                $count = getLinksNumber($row['id']);
                $title .= "({$count})";
            }
            
            $obj_child[] = [
                'value' => $row['id'],
                'label' => $title,
                'disabled' => $disabled,
                'children' => $children
            ];
        }
        
        $stmt->close();
    }
    
    return $obj_child;
}

/**
 * 获取分类下的文章数量
 * @param int $classid 分类ID
 * @return int 文章数量
 */
function getLinksNumber($classid)
{
    global $link;
    $number = 0;
    
    $sql = "SELECT COUNT(id) AS num FROM tb_links WHERE classid LIKE ?";
    $stmt = $link->prepare($sql);
    $search = '%"' . $classid . '"%';
    
    if ($stmt && $stmt->bind_param('s', $search) && $stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $number = (int)$row['num'];
        $stmt->close();
    }
    
    return $number;
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);