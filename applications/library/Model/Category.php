<?php
/**
 * category 表接口
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Model_Category {
    
    public static $table = 'zwshd_category';
    
    /**
     * 从配置表拿值
     * @param string $key
     * @return array $rs
     */
    public static function getAllCategory() {
        $rs = Array();
        $slave = Core_Pdo::factory('slave');
        $sql = 'SELECT `id`, `key`,`name`,`p_id` FROM '.self::$table.' WHERE is_deleted = 0';
        $dbResult = $slave->allPrepare($sql);
        if (!$dbResult->isEmpty() && is_array($dbResult->data)) {
             $rs = $dbResult->data;
        }
        return $rs;
    }    
    
}
