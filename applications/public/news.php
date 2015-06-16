<?php
/**
 * 首页
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */

require dirname(__FILE__).'/../init/init.php';
$data = new Data_News_Query();
if($data->appCacheRead){
    require APPLICATION_PATH.'/header/cache.php';
} else {
    require APPLICATION_PATH.'/header/nocache.php';
}
require APPLICATION_PATH.'/header/cache.php';
$param = isset($_REQUEST['p']) ? $_REQUEST['p']: '';
$aryParam = explode('|', $param);
$id = $aryParam[2];
$id = App_Des::decrypt($id);
$data->field = 'id,create_time,title,detail,category_id,source_name';
$data->id = $id;
$val['news'] = App_News::getOneNews($data);
if(empty($val['news'])){
    header('Location: /404.html');
    exit();
}
$val['title'] = $val['news']['title'];
include $GLOBALS['config']['view']['basePath'].'/news.html';