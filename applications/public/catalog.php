<?php
/**
 * 首页
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */

require dirname(__FILE__).'/../init/init.php';
$data = new Data_Category_News();
if($data->appCacheRead){
    require APPLICATION_PATH.'/header/cache.php';
} else {
    require APPLICATION_PATH.'/header/nocache.php';
}
require APPLICATION_PATH.'/header/cache.php';
$param = isset($_REQUEST['p']) ? $_REQUEST['p']: '';
$aryParam = explode('|', $param);
$cat = $aryParam[1];
$data->page = isset($_REQUEST['pg']) ? intval($_REQUEST['pg']) : 1 ;
$data->category = $cat;
$data->limit = $GLOBALS['const']['cat']['num'];
$val = App_Category::getCatNews($data);
if(empty($val)){
    header('Location: /404.html');
    exit();
}
include $GLOBALS['config']['view']['basePath'].'/catalog.html';