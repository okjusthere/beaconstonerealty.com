<?php
//获取产品属性表信息
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

$code = 200;  //响应码
$message = 'success';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

$obj["data"] = getProductAttributeClass($link);

//获取产品属性分类信息
function getProductAttributeClass(mysqli $link, $parent_id = 0): array
{
    $data = [];
    $sql = "select * from tb_product_attribute_class where parentid=? and is_show='2' order by is_top desc,sort desc,id asc";
    //准备SQL语句
    $stmt = $link->prepare($sql);
    //绑定参数
    $stmt->bind_param('i', $parent_id);
    //执行SQL语句
    if ($stmt->execute()) {
        //获取查询结果
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["children"] = getProductAttributeClass($link, $row["id"]);
            $row["attribute"] = getAttributeValue($link, $row["id"]);

            $data[] = $row;
        }
    }
    //关闭语句
    $stmt->close();

    return $data;
}

/*获取产品属性分类对应的属性值
 * @param int $id 产品属性分类id*/
function getAttributeValue(mysqli $link, $id): array
{
    $data = array();

    $sql = "select id,value,sort from tb_product_attribute_value where attribute_class=? order by sort desc,id asc";
    //准备SQL语句
    $stmt = $link->prepare($sql);
    //绑定参数
    $stmt->bind_param('i', $id);
    //执行SQL语句
    if ($stmt->execute()) {
        //获取查询结果
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $attribute_info = ["id" => $row["id"], "value" => $row["value"], "sort" => $row["sort"]];
            $data[] = $attribute_info;
        }
    }

    return $data;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
