<?php
//路由设置
//链接数据库
include_once 'wf-config.php';
global $link;

//引用全局参数
include_once 'application/index/basic.php';

// 获取请求路径
//$request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$request_path = isset($_GET['url']) ? ltrim($_GET['url'], '/') : '';

try {
    $position_key = ""; //判断调用哪个表的SEO语句
    // 首页处理
    if (empty($request_path)) {
//        $request_path = "index";
        $route_page = "home.html";
    } else {
        // 从数据库加载所有路由规则（可缓存）
        $rules = [];
        $sql = "SELECT trr.id,trr.static_url,trr.params,trt.route_page,trt.position_key FROM tb_rewrite_rules trr LEFT JOIN tb_route_template trt ON trr.template_id=trt.id WHERE trr.static_url=?";
        //准备SQL语句
        $stmt = $link->prepare($sql);
        //绑定参数
        $stmt->bind_param('s', $request_path);
        //执行语句
        if (!$stmt->execute()) throw new Exception('SQL执行失败' . $link->error);

        //获取结果
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $rule = $result->fetch_assoc(); //获取查询结果
            $params = json_decode($rule["params"], true); //路由的相关参数
            $route_page = $rule["route_page"]; //对应的实际页面
            $position_key = (isset($params["use_from"]) && $params["use_from"] === "page" && $rule["position_key"] === "product") ? 'page' : $rule["position_key"]; //引用关键词，用来判断调用哪个表的SEO信息(如果use_from是page，并且模板ID是产品列表：11，产品分类：12，显示所用产品或产品分类，为了seo信息能正常显示，给$position_key赋值page)

            // 设置GET参数
            foreach ($params as $key => $value) {
                $_GET[$key] = $value;
            }
        } else if (preg_match('/^proclass\/(\d+)$/', $request_path, $match)) {
            //看当前访问的伪静态，是否匹配特定规则，用于产品分类列表页页面跳转的判断
            $route_page = "proclass.html";
            $_GET["id"] = (int)$match[1];
            $position_key = "product";
        }else if(preg_match('/^search\/(\d+)$/', $request_path, $match_s)){
            //看当前访问的伪静态，是否匹配特定规则，用于搜索页面跳转的判断
            $route_page = "search.html";
            $_GET["id"] = 0;
        } else {
            throw new Exception('未查到相关路由，或路由不唯一');
        }
    }

    //判断相关页面是否存在
    if (file_exists($route_page)) {
        ob_start();// 开始输出缓冲
        include $route_page; //引用页面模板
        $content = ob_get_clean(); //获取当前输出缓冲区内容并关闭缓冲区

        //获取SEO信息，拼接给前端展示，解决了查看源代码，看不到TDK的问题
        $id = (isset($_GET['id']) && $route_page !== "home.html") ? $_GET['id'] : 0;
        $meta = "\n\t<meta charset=\"utf-8\">\n";
        $meta .= getSEO($link, $id, $position_key);
        //存储请求链接参数和对应值的meta标签
        foreach ($_GET as $key => $value) {
            if ($key !== 'url') {
                $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                if (isset($params["use_from"]) && $params["use_from"] === "page" && $rule["position_key"] === "product" && $key === "id") {
                    $escapedValue = '0';
                }
                $meta .= "\t<meta name=\"{$key}\" content=\"{$escapedValue}\">\n";
            }
        }
        //将对应的静态页一起传到页面上
        $page_name = pathinfo($route_page, PATHINFO_FILENAME);
        $meta .= "\t<meta name=\"route_page\" content=\"{$page_name}\">\n";
        $meta = rtrim($meta, "\n"); //移除最后的换行符
        // 将 meta 插入到 head 标签后
        $content = preg_replace('/<head([^>]*)>/i', '<head$1>' . $meta, $content);

        // 输出最终内容
        echo $content;
    } else {
        throw new Exception('未找到相关页面');
    }
} catch (Exception $err) {
    //print_r($err->getMessage()); //返回报错
    header("HTTP/1.0 404 Not Found");
    include '404.html';
} finally {
    // 清理资源
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}

//获取各个页面设置的的tdk
function getSEO(mysqli $link, $id = 0, $type = ''): string
{
    $seo = getWebSeoInfo($link); //获取网站优化设置的SEO信息
    $seo_title = $seo["seo_title"];
    $seo_keywords = $seo["seo_keywords"];
    $seo_description = $seo["seo_description"];
    $contact = getWebInfo($link); //获取网站联系信息-用来拼接SEO信息
    $company = $contact['company']; //公司名称

    if ($id > 0 && !empty($type)) {
        $sql = ""; //获取SEO的SQL语句
        switch ($type) {
            case 'page':
            case 'news_detail':
                $sql = "SELECT title,seo_title,seo_keywords,seo_description FROM news WHERE id=?";
                break;
            case 'news':
                $sql = "SELECT title,seo_title,seo_keywords,seo_description FROM news_class WHERE id=?";
                break;
            case 'product_detail':
                $sql = "SELECT title,seo_title,seo_keywords,seo_description FROM product WHERE id=?";
                break;
            case 'product':
                $sql = "SELECT title,seo_title,seo_keywords,seo_description FROM product_class WHERE id=?";
                break;
        }
        $stmt = $link->prepare($sql); //准备SQL语句
        $stmt->bind_param('i', $id); //绑定参数
        $stmt->execute(); //执行查询
        $result = $stmt->get_result(); //获取查询结果
        $seo_info = $result->fetch_assoc();
        $seo_title = empty($seo_info["seo_title"]) ? "{$seo_info["title"]}-{$company}" : $seo_info["seo_title"];
        $seo_keywords = empty($seo_info["seo_keywords"]) ? $seo_keywords : $seo_info["seo_keywords"];
        $seo_description = empty($seo_info["seo_description"]) ? $seo_description : $seo_info["seo_description"];
        $stmt->close();
    }

    return "\t<title>{$seo_title}</title>\n\t<meta name=\"description\" content=\"{$seo_description}\">\n\t<meta name=\"keywords\" content=\"{$seo_keywords}\">\n";
}

mysqli_close($link);