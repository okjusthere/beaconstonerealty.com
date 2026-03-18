<?php
//获取管理员表信息

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
if (!my_power("admin_list")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
$where = '';
$params = array();

if (!empty($keywords)) {
    $where = " WHERE username LIKE ?";
    $params[] = "%{$keywords}%";
}

// 准备主查询SQL
$sql = "SELECT * FROM admin" . $where . " ORDER BY id DESC";
$stmt = $link->prepare($sql);

if ($stmt) {
    // 绑定参数
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // 所有参数都是字符串类型
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        $res = $stmt->get_result();
        $code = 200;
        $message = 'success';

        while ($row = $res->fetch_assoc()) {
            //解密账号密码
            $row["password"] = my_crypt($row["password"], 2);
            //获取账号角色名
            $row["powername"] = getPowerName($row["power"]);
            $obj["data"][] = $row;
        }
    } else {
        $message = '查询执行失败: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $message = '预处理语句准备失败: ' . $link->error;
}

/*获取角色名称
 * 参数$id：管理员角色ID*/
function getPowerName($id)
{
    global $link;
    $power_name = "";
    
    $sql_power = "SELECT powername FROM admin_power WHERE id=?";
    $stmt_power = $link->prepare($sql_power);
    
    if ($stmt_power) {
        $stmt_power->bind_param("i", $id);
        if ($stmt_power->execute()) {
            $res_power = $stmt_power->get_result();
            if ($res_power->num_rows > 0) {
                $power_name = $res_power->fetch_assoc()["powername"];
            }
        }
        $stmt_power->close();
    }
    
    return $power_name;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);