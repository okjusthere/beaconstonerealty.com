<?php
//按钮 删除文章分类信息
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '删除失败，请重试！';  //响应信息
$obj = array();

header('Content-Type:application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'));  //获取非表单数据，并用json_decode解码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据');
    exit;
}

// 初始化变量
$id = $data->id ?? 0; //文章分类ID
$rule_id = $data->rule_id ?? 0; //规则ID

//验证必要字段
if ($id <= 0 || $rule_id <= 0) {
    echo $jsonData->jsonData(400, '缺少必要参数，删除失败');
    exit;
}

//判断后台用户权限
if (!my_power("newsclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit; //终止继续执行
}

try {
    // 开启事务
    mysqli_autocommit($link, false);

    // 1.删除文章分类
    if (!deleteRecord('news_class', [$id], ["id"])) throw new Exception('删除文章分类失败');

    //2.删除路由规则
    if (!deleteRecord('tb_rewrite_rules', [$rule_id], ["id"])) throw new Exception('删除文章分类路由失败');

    updatelogs("文章管理，删除文章分类，ID：{$id}；删除对应路由规则，ID：{$rule_id}"); //记录操作日志

    // 提交事务
    mysqli_commit($link);

    $code = 200;
    $message = 'success';

} catch (Exception $e) {
    // 回滚事务
    if (isset($link)) {
        mysqli_rollback($link);
        mysqli_autocommit($link, true);
    }

    $code = 100;
    $message = $e->getMessage();
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);