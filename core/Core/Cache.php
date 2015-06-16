<?php
/**
 * 缓存部分
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Cache {
    
    private static $_memcache = null;
    private static $_local = null;
    private static $_redis = null;
    
    public static $config = array('memcache' => array(),
                                 'redis' => array()
    );
    
    /**
    * cache工厂方法
     *
     * @param $adapter string           
     * @return mixed
     */
    static public function factory($adapter = 'memcache') {
        if (!empty($adapter)) {
            return self::getInstance($adapter);
        } else {
            return false;
        }
    }
    
    static private function getInstance($adapter) {
        switch ($adapter) {
            case 'memcache' :
                if (empty(self::$config['memcache'])) {
                    throw new Core_Exception_SystemException(1001);
                }
                if (empty(self::$_memcache)) {
                    self::$_memcache = new Core_Cache_Memcache(self::$config['memcache']);
                }
                return self::$_memcache;
                break;           
            case 'redis' :
                if (empty(self::$config['redis'])) {
                    throw new Core_Exception_SystemException(1001);
                }
                if (empty(self::$_redis)) {
                    self::$_redis = new Core_Cache_Redis(self::$config['redis']);
                }
                return self::$_redis;
                break;
            default :
                return false;
                break;
        }
    }
}
