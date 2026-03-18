<?php
// PHP built-in server router (用于 Railway 部署)
// 替代 Apache .htaccess 的 URL 重写功能

<?php
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
}
?>
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// 1. 如果请求的是真实存在的文件（CSS/JS/图片/字体等），直接返回
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    // 让 PHP 内置服务器处理静态文件
    return false;
}

// 2. 如果请求的是 application/ 下的 PHP 文件（API），直接执行
if (preg_match('#^/application/#', $path)) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        include $file;
        return;
    }
}

// 3. 其他所有请求 → 走路由（模拟 .htaccess 的 RewriteRule）
$url = ltrim($path, '/');
if (!empty($url)) {
    $_GET['url'] = $url;
}

// 合并 query string 参数
$query = parse_url($uri, PHP_URL_QUERY);
if ($query) {
    parse_str($query, $params);
    $_GET = array_merge($_GET, $params);
}

require __DIR__ . '/route.php';
