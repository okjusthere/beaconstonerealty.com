<?php
//获取嵌入代码信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();
$basic = new \basic\Basic();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//sql查询语句
$sql = "select state from tb_traffic_statistics_state where id=1";

//获取查询结果集
$res = my_sql($sql);

if ($res) {
    $row = mysqli_fetch_assoc($res);
    $state = (int)$row["state"]; //获取当前状态，若为2，则未开启流量统计功能，若为1则进行网站流量统计
    $obj["data"] = array(); //定义一个空数组
    //如果开启了流量统计，才进行访客记录
    if ($state === 1) {
        $sql_info = "select * from tb_traffic_statistics";
        $res_info = my_sql($sql_info);
        if ($res_info) {
            $code = 200; //响应码
            $message = 'success'; //响应信息
            while ($row = mysqli_fetch_assoc($res_info)) {
                $row["page_view"] = json_decode($row["page_view"]);
                $obj["data"][] = $row;
            }
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
