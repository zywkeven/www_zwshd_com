<?php
/**
 * Pdo连接数据库
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Pdo {

    public static $config;
    
    private static $instance;
    
    public static function factory($database = 'local', $dbname = 'default') {

       if ($database == 'master') {
            $config = self::$config[$dbname]['master'];
        } elseif ($database == 'local') {
            $config = self::$config[$dbname]['local'];
        } else {
            $config = self::$config[$dbname]['slave'][array_rand(self::$config[$dbname]['slave'])];
        }       
        return self::getDbInstrance($config);
    }

    public static function getDbInstrance($data, $dbType = 'mysql') {        
        $key = $data["host"] . "_" . $data["database"] . "_" . $data["username"];
        if (!isset(self::$instance[$key]) || !is_object(self::$instance[$key])) {
            try {
                self::$instance[$key] = new Core_Db_MyPdo($data["host"], $data["database"], $data["username"], $data["password"], $dbType);
                if(self::$instance[$key]) {
                    self::$instance[$key]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
            } catch (Exception $ex){
                throw new Core_Exception_SystemException(1001);
            }
        }
        return self::$instance[$key];
    }

}