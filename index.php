<?php
// PHP built-in server router (用于 Railway 部署)
// 替代 Apache .htaccess 的 URL 重写功能

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// 1. 如果请求的是真实存在的文件（CSS/JS/图片/字体等），直接返回
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    // 设置正确的 Content-Type
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'font/otf',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'txt' => 'text/plain',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $path);
        return;
    }
    // PHP 文件直接执行
    if ($ext === 'php') {
        include __DIR__ . $path;
        return;
    }
    // 其他文件让 PHP 处理
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
