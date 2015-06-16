<?php
/**
 * 应用初始化
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */

//定义应用路径
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', dirname(__FILE__) . '/..');

//加载配置
$config = include(APPLICATION_PATH . '/configs/view.ini');
$config += include(APPLICATION_PATH . '/configs/sys.ini');
$config += include(APPLICATION_PATH . '/configs/app.ini');
$config += include(APPLICATION_PATH . '/configs/db.ini');
$config += include(APPLICATION_PATH . '/configs/cache.ini');
$config += include(APPLICATION_PATH . '/configs/const.ini');

//加载timezone
if (!empty($config['common']['timezone'])) {
    date_default_timezone_set($config['common']['timezone']);
}
//Autoloader
require_once $config['library']['coreLibrary'] . '/Core/Loader.php';
$saveLib = Array();
foreach ($config['library'] as $lib){
    $saveLib[] = $lib;
}
Core_Loader::setBasePath($saveLib);

//初始化Db配置
Core_Pdo::$config = $config['db'];
//初始化Cache配置
Core_Cache::$config['memcache'] = $config['cache'];
//初始化redis配置
Core_Cache::$config['redis'] = $config['redis'];

//url获取缓存控制参数
App_Parameter::getCacehState();
//缓存开关
$enableCache = $GLOBALS['config']['app']['enableCache'];
//视图类参数
$view = $config['view'];
//常量
$const = $config['const'];
