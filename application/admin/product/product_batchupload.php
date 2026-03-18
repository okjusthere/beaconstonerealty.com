<?php
// 添加文章信息
// 查看是否有访问权限
include_once '../checking_user.php';
// 链接数据库
include_once '../../../wf-config.php';
global $link;

include_once "../function.php"; //引用自定义函数
include_once '../../../myclass/ResponseJson.php'; // 获取自定义的返回json的函数

class myData
{
    use \myclass\ResponseJson\ResponseJson;
}

$jsonData = new myData();

$data = json_decode(file_get_contents('php://input')); //获取非表单数据，并解用json_decode码axios传递过来的json数据;
if (json_last_error() !== JSON_ERROR_NONE) {
    echo $jsonData->jsonData(400, '无效的数据', []);
    exit;
}

// 初始化变量
$paixu = 0;
$classid = (isset($data->classid) && is_array($data->classid) && count($data->classid) > 0) ? json_encode($data->classid) : '';
$template_id = $data->template_id ?? 0; //产品详情模板ID
$title = $data->title ?? '';
$specifications = "";
$origin = "";
$price = 0.00;
$keywords = "";
$is_top = 1;
$is_show = 1;
$description = "";
$thumbnail = "";
$enclosure = "";
$photo_album = "";
$content = "";
$add_time = time();
$seo_title = "";
$seo_keywords = "";
$seo_description = "";

$producttitle = $data->producttitle ?? ''; //是否使用图片名作为标题
$path = $data->path ?? []; //批量上传的图片信息

$code = 500;
$message = '未响应，请重试！';
$obj = array();

header('Content-Type:application/json; charset=utf-8');

// 判断后台用户权限
if (!my_power("product_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit;
}

// 验证验证必要字段
if (empty($path) || empty($template_id)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再保存', []);
    exit;
}

try {
    // 开启事务
    mysqli_autocommit($link, false);

    // 1.准备插入语句，添加产品信息
    $sql = "INSERT INTO product (classid, title, specifications, origin, price, keywords, 
            description, thumbnail, enclosure, photo_album, content, add_time, is_show, 
            is_top, paixu, seo_title, seo_keywords, seo_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        throw new Exception('数据库准备失败!');
    }

    $successInsertId = []; // 上传成功的ID数组

    foreach ($path as $value) {
        if ($producttitle === "1") {
            $path_name = ((array)$value)["name"];
            $title = substr($path_name, 0, strripos($path_name, '.')); //去除后缀的图片名
        }
        $thumbnail = json_encode([$value], JSON_UNESCAPED_UNICODE);

        // 绑定参数
        $bind_result = $stmt->bind_param('ssssdsssssssssisss',
            $classid, $title, $specifications, $origin, $price, $keywords,
            $description, $thumbnail, $enclosure, $photo_album, $content,
            $add_time, $is_show, $is_top, $paixu, $seo_title, $seo_keywords, $seo_description);
        if (!$bind_result) {
            throw new Exception('绑定参数失败!');
        }

        // 执行插入
        $result = $stmt->execute();
        if (!$result) {
            throw new Exception('插入失败，请检查数据是否合规!');
        }

        $successInsertId[] = $stmt->insert_id;
    }

    // 2.给插入成功的文章，生成对应的路由
    $route_page = getRoutePage($template_id); //获取路由模板信息
    foreach ($successInsertId as $item) {
        $static_ur = $route_page . '/' . $item;
        $rule_id = insertRewriteRules($static_ur, $template_id, '1', json_encode(["id" => $item]));
        if ($rule_id === 0) {
            throw new Exception('路由信息添加失败，请联系管理员！');
        }

        //路由插入成功，更新对应的文章路由ID
        $updateData = ["rule_id" => $rule_id];
        if (!updateRecord('product', [$item], ["id"], $updateData)) {
            throw new Exception('产品路由ID更新失败');
        }
    }

    $success_id = implode(',', $successInsertId);
    // 记录操作日志
    $updateTips = "产品管理，批量添加产品，ID：{$success_id}";
    updatelogs($updateTips);

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

} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

// 关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);