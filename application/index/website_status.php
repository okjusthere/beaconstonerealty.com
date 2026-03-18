<?php
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//获取留言信息
$sql_message = "select count(id) as number,(select count(id) from message where reply is not null) as reply from message";
$res_message = my_sql($sql_message);
if ($res_message) {
    $info_message = mysqli_fetch_assoc($res_message);
}

//获取七天内留言信息
$sql_message_seven = "select count(id) as number from message where add_time BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY)) AND UNIX_TIMESTAMP(NOW())";
$res_message_seven = my_sql($sql_message_seven);
if ($res_message_seven) {
    $info_message["number_seven"] = mysqli_fetch_assoc($res_message_seven)["number"];
}

/*返回信息
web_state：网站状态
message：网站留言信息{number：留言条数；reply：回复了的条数}*/
$obj = array("web_state" => "success", "message" => $info_message, "traffic_statistics" => getTrafficStatistics());

//关闭数据库链接
mysqli_close($link);

//获取流量信息
function getTrafficStatistics()
{
    $data = array();
    $sql = "select * from tb_traffic_statistics";
    $res = my_sql($sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row["page_view"] = json_decode($row["page_view"]);
            $data[] = $row;
        }
    }
    return $data;
}

echo $jsonData->jsonData($code, $message, $obj);
