<?php
//添加文章信息
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

$data = file_get_contents('php://input'); //获取非表单数据;
$data = json_decode($data); //解码axios传递过来的json数据

// 初始化变量
$paixu = 0; //文章排序
$classid = (isset($data->classid) && is_array($data->classid) && count($data->classid) > 0) ? json_encode($data->classid) : ''; //所属分类ID
$template_id = $data->template_id ?? 0; //文章详情模板ID
$title = $data->title ?? ''; //文章标题
$url = ""; //文章链接地址
$keywords = ""; //文章关键词
$is_top = 1; //不置顶
$is_show = 1; //显示
$description = ""; //文章描述
$thumbnail = ""; //缩略图
$enclosure = ""; //附件
$photo_album = ""; //图片相册
$content = ""; //详情内容
$add_time = time(); //添加时间
$seo_title = ""; //SEO标题
$seo_keywords = ""; //SEO关键词
$seo_description = ""; //SEO描述

$newstitle = $data->newstitle ?? ''; //是否使用图片名作为标题
$path = $data->path ?? []; //批量上传的图片信息

$code = 500;  //响应码
$message = '未响应，请重试！';  //响应信息
$obj = []; //返回对象（数据）

header('Content-Type:application/json; charset=utf-8');

//判断后台用户权限
if (!my_power("news_add")) {
    echo $jsonData->jsonData(201, '您没有管理该页面的权限！', []);
    exit; //终止继续执行
}

// 验证验证必要字段
if (empty($path) || empty($template_id)) {
    echo $jsonData->jsonData(400, '缺少必要字段，请检查后再保存', []);
    exit;
}

try {
    // 开启事务
    mysqli_autocommit($link, false);

    // 1.准备插入语句，添加文章信息
    $sql = "INSERT INTO news (classid, title, url, keywords, description, thumbnail, enclosure, photo_album, content, add_time, is_show, is_top, paixu, seo_title, seo_keywords, seo_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        throw new Exception('数据库准备失败!');
    }

    $successInsertId = []; // 上传成功的ID数组

    foreach ($path as $value) {
        if ($newstitle === "1") {
            $path_name = ((array)$value)["name"];
            $title = substr($path_name, 0, strripos($path_name, '.')); //去除后缀的图片名
        }
        $thumbnail = json_encode([$value], JSON_UNESCAPED_UNICODE);

        // 绑定参数
        $bind_result = $stmt->bind_param("ssssssssssssisss",
            $classid, $title, $url, $keywords, $description,
            $thumbnail, $enclosure, $photo_album, $content,
            $add_time, $is_show, $is_top, $paixu,
            $seo_title, $seo_keywords, $seo_description
        );
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
        if (!updateRecord('news', [$item], ["id"], $updateData)) {
            throw new Exception('新闻路由ID更新失败');
        }
    }

    $success_id = implode(',', $successInsertId);
    // 记录操作日志
    $updateTips = "文章管理，批量添加文章，ID：{$success_id}";
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

//关闭数据库链接
mysqli_close($link);

echo $jsonData->jsonData($code, $message, $obj);