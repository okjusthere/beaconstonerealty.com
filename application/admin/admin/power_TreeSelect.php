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
$obj = ['data' => []]; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 准备预处理语句
$sql = 'SELECT id, powername FROM admin_power ORDER BY sort DESC, id ASC';
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
        // 获取结果集
        $result = $stmt->get_result();
        if ($result) {
            $code = 200;
            $message = '查询成功！';
            
            // 遍历结果集
            while ($row = $result->fetch_assoc()) {
                $obj["data"][] = [
                    "value" => (int)$row["id"],
                    "label" => $row["powername"]
                ];
            }
        }
    }
    // 关闭语句
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);