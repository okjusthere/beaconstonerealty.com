<?php
//远程数据请求链接
define("SITEMANAGE_URL", "https://sitemanage.webforce.com.cn");
//获取模板后台信息（logo、背景图等）
define("API_BG_INFO", SITEMANAGE_URL . "/page/config/basic.json");
//获取业务信息
define("API_MARKING", SITEMANAGE_URL . "/page/config/marking.json");
//获取相关网站信息
define("API_WEBINFO", SITEMANAGE_URL . "/page/getwebinfo.php?domain=");
//验证管理员信息
define("API_CHECK_ADMIN", SITEMANAGE_URL . "/page/administrator/login.php");