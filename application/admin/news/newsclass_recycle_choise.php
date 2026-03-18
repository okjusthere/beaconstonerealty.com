<?php
//复选框 文章分类放入回收站
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
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("newsclass_delete")) {
    $code = 201;  //响应码
    $message = '您没有管理该页面的权限！';  //响应信息
    echo $jsonData->jsonData($code, $message, $obj);
    die(); //终止继续执行
}

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据
//$del_id = '';
//foreach ($data as $key => $val) {
//    $del_id .= $val . ',';
//}
//$del_id = trim($del_id, ','); //移除变量$del_id最后一个逗号
$del_id = implode(',', $data); //将传递过来要删除的ID数组，通过implode函数转换成字符串用逗号隔开

// 删除语句
$sql = "update news_class set is_delete=1 where id in ({$del_id})";
// 如果有作为父级的分类被删除，将其子栏目改成顶级分类
$sql_update = "update news_class set parentid=0 where parentid in ({$del_id})";

// 执行更新
$res_update = my_sql($sql_update);
if ($res_update) {
    // 放入回收站
    $res = my_sql($sql);
    if ($res) {
        $code = 200;
        $message = 'success';

        updatelogs("文章管理，文章分类批量放入回收站，ID：" . $del_id); //记录操作日志
    } else {
        $code = 100;
        $message = '删除失败，请重试！';
    }
} else {
    $code = 100;
    $message = '未知的错误，请联系管理员！';
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);