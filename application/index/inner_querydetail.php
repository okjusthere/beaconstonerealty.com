<?php
//获取查询表信息
//链接数据库
include_once '../../wf-config.php';
global $link;
include_once 'check.php';
//引用自定义函数
include_once "function.php";

//获取自定义的返回json的函数
include_once '../../myclass/ResponseJson.php';
include_once '../../myclass/Basic.php'; // 引用自定义函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$q_condition = isset($_POST['q_condition']) ? \basic\Basic::filterStr($_POST['q_condition']) : ''; //查询条件


if (!empty($q_condition)) {
    $sql_field_custom = "select * from field_custom where table_name='query'";
    $res_field_custom = my_sql($sql_field_custom); //查看是否有相关的自定义字段
    $field_select = ""; //将自定义字段的内容一起输出
    if ($res_field_custom) {
        if (mysqli_num_rows($res_field_custom) > 0) {
            while ($row = mysqli_fetch_assoc($res_field_custom)) {
                $field_select .= ",(select field_content from field_info where table_name='query' and field_name='{$row["field_name"]}' and record_id=(select id from query where q_condition='{$q_condition}' and state=1 ORDER BY id desc LIMIT 0,1)) as {$row["field_name"]}";
            }
        }
    }

    //sql查询语句
    $sql = "select *{$field_select} from query where q_condition='{$q_condition}' and state=1 ORDER BY id desc LIMIT 0,1"; //如果有多个相同的，只取第一个

    //获取查询结果集
    $res = my_sql($sql);
    if ($res) {
        $code = 200;
        $message = '查询成功！';
        $obj["data"] = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row["thumbnail"] = getThumbnailPath(object_array(json_decode($row["thumbnail"])));
            $obj["data"] = $row;
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
