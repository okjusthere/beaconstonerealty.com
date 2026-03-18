<?php
//获取文章表信息
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
$obj = array("data" => array(), "total" => 0); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("query_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

// 获取并验证分页参数
$currentpage = isset($_GET['currentPage']) ? max(1, (int)$_GET['currentPage']) : 1;
$pagesize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize']) : 10;
$del = isset($_GET['del']) ? (int)$_GET['del'] : 0;
$keywords = isset($_GET['keywords']) ? trim(rawurldecode($_GET['keywords'])) : '';

// 准备SQL查询
$sql = "SELECT * FROM query WHERE is_delete = ?";
$params = array($del);
$types = "i"; // 参数类型

// 添加关键词搜索条件
if (!empty($keywords)) {
    $sql .= " AND (q_condition LIKE ?)";
    $params[] = "%{$keywords}%";
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

// 查询总记录数
$stmt_total = $link->prepare($sql);
if ($stmt_total) {
    // 动态绑定参数
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt_total, 'bind_param'), $bind_names);
    
    $stmt_total->execute();
    $res_total = $stmt_total->get_result();
    
    if ($res_total) {
        $total = $res_total->num_rows;
        $obj["total"] = $total;
        
        // 添加分页限制
        $start = ($currentpage - 1) * $pagesize;
        $sql .= " LIMIT ?, ?";
        $params[] = $start;
        $params[] = $pagesize;
        $types .= "ii";
        
        // 查询分页数据
        $stmt = $link->prepare($sql);
        if ($stmt) {
            // 重新绑定参数（包含分页参数）
            $bind_names = array();
            $bind_names[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt, 'bind_param'), $bind_names);
            
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res) {
                $code = 200;
                $message = '查询成功！';
                
                while ($row = $res->fetch_assoc()) {
                    $row["thumbnail"] = json_decode($row["thumbnail"]);
                    $obj["data"][] = $row;
                }
            }
            $stmt->close();
        }
    }
    $stmt_total->close();
}

//关闭数据库链接
mysqli_close($link);
echo $jsonData->jsonData($code, $message, $obj);