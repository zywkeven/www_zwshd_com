<?php
/**
 * news 表接口
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Model_News {
    
    public static $table = 'zwshd_news';
    
    /**
     * 
     * @return multitype:
     */
    public static function getNews(Data_News_Query $data) {
        $rs = Array();
        $slave = Core_Pdo::factory('slave');
        $sql = 'SELECT '.$data->field.' FROM '.self::$table.' WHERE is_deleted = 0';
        $param = Array();
        if(!is_null($data->category_id)){
            $sql .= ' AND category_id = :category_id';
            $param[':category_id'] = $data->category_id;
        }
        if(!is_null($data->orderBy)){
            $sql .= ' ORDER BY '.$data->orderBy;
        }
        if(!is_null($data->limit)){
            $sql .= ' LIMIT '.$data->limit;
        }
        $dbResult = $slave->allPrepare($sql, $param);
        if (!$dbResult->isEmpty() && is_array($dbResult->data)) {
             $rs = $dbResult->data;
        }
        return $rs;
    }
    
    /**
     * 
     * @param Data_News_Query $data
     * @return multitype:
     */
    public static function getOneNews(Data_News_Query $data){
        $rs = Array();
        $slave = Core_Pdo::factory('slave');
        $sql = 'SELECT '.$data->field.' FROM '.self::$table.' WHERE is_deleted = 0';
        $param = Array();
        if(!is_null($data->id)){
            $sql .= ' AND id = :id';
            $param[':id'] = $data->id;
        }
        if(!is_null($data->category_id)){
            $sql .= ' AND category_id = :category_id';
            $param[':category_id'] = $data->category_id;
        }
        if(!is_null($data->orderBy)){
            $sql .= ' ORDER BY '.$data->orderBy;
        }
        if(!is_null($data->limit)){
            $sql .= ' LIMIT 0,'.$data->limit;
        }
        $dbResult = $slave->rowPrepare($sql, $param);
        if (!$dbResult->isEmpty() && is_array($dbResult->data)) {
            $rs = $dbResult->data;
        }
        return $rs;
    }
    
    
    
}
