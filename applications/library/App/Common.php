<?php
/**
 * 公用类
 * @author Keven.Zhong
 * @Version 1.0 At 2014-04-15
 */
class App_Common{
   
    private static $logger = null;
    /**
     * 返回缓存过期时间
     * 
     */
    public static function getCacheTime(
        $maxCacheTime = null, $minCacheTime = null){
         $config = $GLOBALS['config'];
         
         $maxCacheTime = $maxCacheTime
            ? $maxCacheTime : $config['app']['maxCacheTime'];
         $minCacheTime = $minCacheTime
            ? $minCacheTime : $config['app']['minCacheTime'];
         
         $requestTime = $_SERVER['REQUEST_TIME'];
         $result = $maxCacheTime - ($requestTime) % $maxCacheTime;
         return $result > $minCacheTime ? $result : $minCacheTime;
    }
    
    /**
     * 写日志
     * @param string $message
     * @param string $level
     */
    public static function addLog($msg, $level){
        if(!self::$logger){
            //日志初始化
            if (file_exists($GLOBALS['config']['app']['loggerPath'])) {
                include_once $GLOBALS['config']['app']['loggerPath'];
            }
            try {
                $logConfig = include(APPLICATION_PATH . '/configs/log.ini');
                Core_Log_Logger::$config = $logConfig;
                Core_Log_Writer_Log4php::$config = $logConfig;
                self::$logger = Core_Log_Logger::getInstance(Core_Log_Logger::WRITER_LOG4PHP);
        
            } catch (Exception $ex) {
            }
        
        }
        if (self::$logger == null || get_class(self::$logger)!='Core_Log_Logger') {
            return;
        }
        switch ($level) { 
            case 'debug':
                self::$logger->debug($msg);
                break;
            case 'info':
                self::$logger->info($msg);
                break;
            case 'warn':
                self::$logger->warn($msg);
                break;
            case 'error':
                self::$logger->error($msg);
                break;
            case 'fatal':
                self::$logger->fatal($msg);
                break;
            default:
                self::$logger->info($msg);
        }
    }
    
    public static function getLink(Data_News_Link $data){
        $link = 'http://www.zwshd.com';
        switch($data->type){
            case 'news':
                $id = App_Des::encrypt($data->id);
                $link .= '/'.$data->parents.'/'.$data->cat.'/'.$id.'.html';
                break;
            case 'cat':
                $link .= '/'.$data->parents.'/'.$data->cat;
                break;
            default:
                break;
        }
        return $link;
    }
}