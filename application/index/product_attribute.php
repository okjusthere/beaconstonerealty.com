<?php
//获取产品属性分类和属性值信息
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

$id = isset($_POST['id']) ? \basic\Basic::filterInt($_POST['id']) : 0; //产品ID

if ($id > 0) {
    $sql = "select * from tb_product_attribute where product_id={$id} order by sort desc,id asc";
    $res = my_sql($sql);
    if ($res) {
        $code = 200;
        $message = '查询成功！';
        while ($row = mysqli_fetch_assoc($res)) {
            $row["attribute_value_id"] = json_decode($row["attribute_value_id"]);
            $row["attribute_value"] = json_decode($row["attribute_value"]);
            $row["photo_album"] = getPhotoAlbumPath(object_array(json_decode($row["photo_album"])));
            //$row["price"] = number_format(floatval($row["price"]), 2); //将价格从string类型转换成浮点型
            $row["inventory"] = (int)$row["inventory"]; //将库存从string类型转换成int类型
            $row["sort"] = (int)$row["sort"]; //将排序从string类型转换成int类型

            $obj["data"][] = $row;
        }
    }
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
