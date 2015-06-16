<?php
//使用动态首页，不可以设置CDN缓存时间
header("Content-type: text/html; charset=utf-8");
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Pragma: no-cache');