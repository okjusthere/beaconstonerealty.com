<?php
// PHP built-in server router (用于 Railway 部署)
// 替代 Apache .htaccess 的 URL 重写功能

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// 1. 如果请求的是真实存在的文件（CSS/JS/图片/字体等），直接返回
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    // PHP 文件 → 切换到该文件的目录后执行（保证相对路径 include 正常）
    if ($ext === 'php') {
        chdir(dirname(__DIR__ . $path));
        include __DIR__ . $path;
        return;
    }

    // 静态资源 → 设置 MIME 类型后输出
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'font/otf',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mp3' => 'audio/mpeg',
        'txt' => 'text/plain',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'map' => 'application/json',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $path);
        return;
    }

    // 其他未知类型文件，让 PHP 内置服务器默认处理
    return false;
}

// 2. 其他所有请求 → 走路由（模拟 .htaccess 的 RewriteRule）
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
