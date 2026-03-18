<?php
//查看是否有访问权限
include_once '../checking_user.php';
//链接数据库
include_once '../../../wf-config.php';
global $link;

//获取自定义的返回json的函数
include_once '../../../myclass/ResponseJson.php';

require_once('../../vendor/autoload.php');
require_once('../../myclass/Excel.php'); // 引用Excel导出类

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}


$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//sql查询语句
$sql = "select * from tb_logs order by id desc";

//获取查询结果集
$res = my_sql($sql);

$data_export = []; //要导出的数据
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data_export[] = $row;
    }
} else {
    $code = 100; //响应码
    $message = '数据获取失败'; //响应信息
}

//关闭数据库链接
mysqli_close($link);

if (count($data_export) > 0) {
    $excel = new \myclass\Excel();

    $header = [
        ['column' => 'A', 'name' => '编号', 'type' => 'autoincrement', 'iscenter' => '1'],
        ['column' => 'B', 'name' => '账号', 'key' => 'admin_name', 'width' => '20'],
        ['column' => 'C', 'name' => '事件', 'key' => 'event', 'width' => '30'],
        ['column' => 'D', 'name' => '操作时间', 'key' => 'operate_time', 'width' => '20', 'iscenter' => '1', 'type' => 'timestamp'],
        ['column' => 'E', 'name' => 'IP地址', 'key' => 'ip_address', 'width' => '20', 'iscenter' => '1'],
    ]; //表头
    $filename = "操作日志"; //导出的Excel文件名

    if ($excel::dataToExcel($header, $data_export, $filename)) {
        $code = 200; //响应码
        $message = 'success'; //响应信息

        $filename .= '.xlsx';
        $obj['name'] = $filename;
        $obj['directory'] = '../../media_library/download/' . $filename;
    }
}

echo $jsonData->jsonData($code, $message, $obj);