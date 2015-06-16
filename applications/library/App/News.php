<?php
/**
 * 新闻内容
 * @author Keven.Zhong
 * @Version:1.0 At 2015-05-29
 */

class App_News {
    
    /**
     * 
     * @param Data_News_Query $data
     * @return Ambigous <NULL, mixed, unknown>
     */
    public static function getOneNews(Data_News_Query $data){
        $rs = null;
        $mem = Core_Cache::factory('memcache');
        $key = $GLOBALS['config']['app']['preMemKey'] . '_' . __CLASS__ . '_' . __FUNCTION__ . '_' .
                md5($data->id,$data->field);
        if($data->appCacheRead) {
            $rs = $mem->get($key);
        }
        if(is_null($rs)){
            $news = Model_News::getOneNews($data);
            $dataCat = new Data_Category_Query();
            $dataCat->total = App_Category::getCategory($dataCat);
            $rs = App_Index::getCatTitle($dataCat, $news['category_id']);
            $rs = array_merge($news, $rs);
            $mem->set($key, $rs, App_Common::getCacheTime(600));
        }
        return $rs;
    }
}