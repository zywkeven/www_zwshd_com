<?php
/**
 * 分类处理
 * @author keven.zhong
 *
 */

class App_Category {
    
    private static $cateogry = null;
    
    /**
     * 
     * @param Data_Category_Query $data
     * @return Ambigous <NULL, multitype:, mixed, unknown>
     */
    public static function getCategory(Data_Category_Query $data){
        $rs = null;
        $mem = Core_Cache::factory('memcache');
        $key = $GLOBALS['config']['app']['preMemKey'] . '_' . __CLASS__ . '_' . __FUNCTION__;
        if($data->appCacheRead) {
            $rs = $mem->get($key);
        }
        if(is_null($rs)){
            $category = Model_Category::getAllCategory();
            $rs = self::transformRelation($category);
            $mem->set($key, $rs, App_Common::getCacheTime(600));
        }
        return $rs;
    }
    
    public static function transformRelation($category){
        $rs = Array();
        foreach ($category as $records){
            if($records['p_id'] == 0){
                $rs[$records['id']]['key'] = $records['key'];
                $rs[$records['id']]['name'] = $records['name'];
            }else {
                $rs[$records['p_id']]['detail'][] = array(
                        'id' => $records['id'],
                        'key' => $records['key'],
                        'name' => $records['name']
                        
                );
            }
        }
        return $rs;
    }
    
    /**
     * 
     * @param Data_Category_News $data
     */
    public static function getCatNews(Data_Category_News $data){
        $rs = null;
        $mem = Core_Cache::factory('memcache');
        $key = $GLOBALS['config']['app']['preMemKey'] . '_' . __CLASS__ . '_' . __FUNCTION__ .
        md5($data->category . $data->limit . $data->page);
        if($data->appCacheRead) {
            $rs = $mem->get($key);
        }
        if(is_null($rs)){
            $rs = Array();
            $dataQuery = new Data_Category_Query();
            $dataQuery->category = array($data->category);
            $dataQuery->total = App_Category::getCategory($dataQuery);
            $catTitle = App_Index::getCatTitle($dataQuery,$data->category);
            if(!empty($catTitle)){
                $rs = $catTitle;
                $rs['title'] = $catTitle['parents']['name']. ' - ' . $catTitle['cat']['name'];
                $dataNews = new Data_News_Query();
                $dataNews->category_id = $catTitle['cat']['id'];
                $dataNews->field = 'id,title,create_time';
                $dataNews->limit = $data->limit;
                $dataNews->orderBy = '`date` DESC,weight ASC,id DESC';
                App_Page::Paging('Model_News', 'getNews',$dataNews, $data->page);
                $news = App_Page::$dbRes;
                $dataLink = new Data_News_Link();
                $dataLink->type = 'news';
                $dataLink->parents = $catTitle['parents']['key'];
                $dataLink->cat = $catTitle['cat']['key'];
                foreach($news as $newsKey => $newVal){
                    $dataLink->id = $newVal['id'];
                    $news[$newsKey]['link'] = App_Common::getLink($dataLink); 
                }
                $rs['news'] = $news;
                $rs['page'] = App_Page::$html;
            }
            $mem->set($key, $rs, App_Common::getCacheTime(600));
        }
        return $rs;
    }
    
}