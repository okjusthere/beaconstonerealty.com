<?php
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
$message = '未响应，请重试！';  //响应信息
$obj = array(); //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//sql查询语句
$sql = "select * from tb_product_attribute_class  where parentid=0 order by is_show desc,is_top desc,sort desc,id asc";

$id_current = isset($_GET['id']) ? $_GET['id'] : 0; //禁止选中的分类ID

//获取查询结果集
$res = my_sql($sql);

if ($res != '500') {

    $code = 200;
    $message = '查询成功！';
    $obj["data"] = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $disabled = false;
        if ($row["id"] == $id_current) {
            $disabled = true;
        }
        //通过当前ID查询相关子分类
        $row["children"] = getChild($row["id"], $id_current, $disabled);

        $info = ["value" => $row["id"], "label" => $row["title"], "disabled" => $disabled, "children" => $row["children"]];
        $obj["data"][] = $info;
    }
}

/*
 * $id 父级分类ID
 * $id_cur 禁止选中的分类ID
 * $child_state 父级分类是否选中true/false
 */
function getChild($id, $id_cur, $child_state)
{
    $obj_child = array();

    $res_child = my_sql("select * from tb_product_attribute_class where parentid={$id} order by is_show desc,is_top desc,sort desc,id asc");
    if ($res_child != '500') {
        while ($row = mysqli_fetch_assoc($res_child)) {
            $row["children"] = getChild($row["id"], $id_cur, $child_state); //找子分类下的子分类
            $disabled = false;
            if ($child_state) {
                $disabled = true;
            } elseif ($row["id"] == $id_cur) {
                $disabled = true;
            }

            $info = ["value" => $row["id"], "label" => $row["title"], "disabled" => $disabled, "children" => $row["children"]];
            $obj_child[] = $info;
        }
    }
    return $obj_child;
}

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);
