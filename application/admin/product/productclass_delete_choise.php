<?php
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; //获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;
$message = '删除失败，请重试！';
$obj = [];

header('Content-Type:application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并用json_decode解码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}

// 初始化变量
$id = $data->id ?? []; //文章分类ID数组
$rule_id = $data->rule_id ?? []; //规则ID数组

//验证必要字段
if (count($id) === 0 || count($rule_id) === 0 || count($id) !== count($rule_id)) {
    echo $jsonData->jsonData(400, '缺少必要参数，删除失败');
    exit;
}

// 判断后台用户权限
if (!my_power("proclass_delete")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！');
    exit;
}

try {
    // 开启事务
    mysqli_autocommit($link, false);

    //1.批量删除产品分类
    if (!deleteRecords('product_class', $id)) throw new Exception('批量删除产品分类失败');

    //2.批量删除产品分类对应路由
    if (!deleteRecords('tb_rewrite_rules', $rule_id)) throw new Exception('批量删除产品分类路由失败');

    $id_log = implode(',', $id);
    $rule_id_log = implode(',', $rule_id);
    updatelogs("产品管理，批量删除产品分类，ID：{$id_log}；删除对应路由规则，ID：{$rule_id_log}");

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