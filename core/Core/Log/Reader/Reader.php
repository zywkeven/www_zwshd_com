<?php
class Core_Log_Reader_Reader
{
    public static $config = array();

    static public function factory($domain)
    {
        if (empty(self::$config)) {
            throw new Core_Exception_ResourceException(5000);
        }
        $logKey=isset(self::$config[$domain]) ? $domain : 'default';
        if (!isset(self::$config[$logKey])) {
            throw new Core_Exception_ResourceException(5000);
        }

        $adapterClassName='Core_Log_Reader_'.ucfirst(self::$config[$domain]['adapter']).'Reader';
        if (class_exists($adapterClassName)) {
            return new $adapterClassName(self::$config[$domain]);
        } else {
            throw new Core_Exception_ResourceException(5001);
        }
    }

}
