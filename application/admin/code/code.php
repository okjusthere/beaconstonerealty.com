<?php
// 获取嵌入代码表信息

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
$obj = []; // 返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

// 准备预处理语句
$sql = 'SELECT * FROM code WHERE id = ?';
$stmt = $link->prepare($sql);

if (!$stmt) {
    $code = 500;
    $message = '数据库查询准备失败: ' . $link->error;
} else {
    // 绑定参数
    $id = 1; // 固定查询id=1的记录
    $stmt->bind_param("i", $id);
    
    // 执行查询
    if (!$stmt->execute()) {
        $code = 500;
        $message = '数据库查询执行失败: ' . $stmt->error;
    } else {
        // 获取结果集
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $code = 200;
            $message = '查询成功！';
            $row = $result->fetch_assoc();
            
            // 处理code字段
            $row["code"] = htmlspecialchars_decode($row["code"]);
            
            // 转换state字段为布尔值
            $row["state"] = ($row["state"] != '1'); // 1转false，其他转true
            
            $obj = $row;
        } else {
            $code = 404;
            $message = '未找到相关记录';
        }
    }
    // 关闭语句
    $stmt->close();
}

// 关闭数据库连接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);