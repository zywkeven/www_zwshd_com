<?php
/**
 * 首页
 * @author Keven.Zhong
 * @Version 1.0 At 2014-05-28
 */

require dirname(__FILE__).'/../init/init.php';
$data = new Data_Category_Query();
if($data->appCacheRead){
    require APPLICATION_PATH.'/header/cache.php';
} else {
    require APPLICATION_PATH.'/header/nocache.php';
}
$val = array();
$data->category = $GLOBALS['const']['index']['category'];
$data->num =  $GLOBALS['const']['index']['num'];
$val['title'] = $GLOBALS['const']['title'];
$val['category'] = App_Index::getAbbreviations($data);
include $GLOBALS['config']['view']['basePath'].'/index.html';