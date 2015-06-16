<?php
/**
 * web_config 表接口
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Model_Config {
    
    /**
     * 从配置表拿值
     * @param string $key
     * @return array $rs
     */
    public static function getConfigValFromDb($key){
        $rs = Array();
        $slave = Core_Pdo::factory('slave');
        $sql = "SELECT config_value,id FROM web_config WHERE config_key = :config_key";
        $param = Array(':config_key' => $key);
        $dbResult = $slave->rowPrepare($sql, $param);
        if (!$dbResult->isEmpty() && is_array($dbResult->data)) {
             $rs = $dbResult->data;
        }
        return $rs;
    }    
    
}
