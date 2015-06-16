<?php
/**
 * 首页内容
 * @author Keven.Zhong
 * @Version： 1.0 At 2014-04-15
 */

class App_Index {
    
    /**
     * 
     * @param unknown $parameter
     * @return Ambigous <NULL, mixed>
     */
    public static function getApiResult($parameter) {
        $obj = Model_OneApi::getInstance();       
        $rs = $obj->getApi($parameter);
        return $rs;
    }
    
    /**
     * 获取缩略信息
     * @return array
     */
    public static function getAbbreviations(Data_Category_Query $data){
        $rs = null;
        $mem = Core_Cache::factory('memcache');
        $key = $GLOBALS['config']['app']['preMemKey'] . '_' . __CLASS__ . '_' . __FUNCTION__ . '_' . 
        md5(serialize($data));
        if($data->appCacheRead) {
            $rs = $mem->get($key);
        }
        if(is_null($rs)){
            $data->total = App_Category::getCategory($data);
            $rs = self::getCategoryNews($data);
            $mem->set($key, $rs, App_Common::getCacheTime(600));
        }
        return $rs;
    }
    
    /**
     * 获取新闻信息
     * @param Data_Category_Query $data
     * @return multitype:unknown
     */
    public static function getCategoryNews(Data_Category_Query $data){
        $rs = Array();        
        if(is_array($data->category)) {
            $dataCat = new Data_News_Query();
            $dataCat->limit = $data->num;
            $dataCat->orderBy = ' `date` DESC,weight ASC,id DESC';
            $dataCat->field = 'id,title,create_time';
            $dataLink = new Data_News_Link();
            $dataLink->type = 'news';
            foreach($data->category as $eachCategory){
                $cat = self::getCatTitle($data, $eachCategory);
                if(!empty($cat)){
                    $dataCat->category_id = $cat['cat']['id'];
                    $cat['list'] = Model_News::getNews($dataCat);
                    foreach($cat['list'] as $listKey => $eachList){
                        $dataLink->parents = $cat['parents']['key'];
                        $dataLink->cat = $cat['cat']['key'];
                        $dataLink->id = $eachList['id'];
                        $cat['list'][$listKey]['link'] = App_Common::getLink($dataLink);
                    }
                    $rs[$eachCategory] = $cat;
                }
            }
        }
        return $rs;
    }

    /**
     * 分类导航
     * @param Data_Category_Query $data
     * @param unknown $eachCategory
     * @return multitype:multitype:unknown
     */
    public static function getCatTitle(Data_Category_Query $data, $eachCategory){
        $rs = Array();
        if(is_array($data->total)){
            $dataLink = new Data_News_Link();
            $dataLink->type = 'cat';
            
            foreach($data->total as $parentId => $parents){
                if(isset($parents['detail']) && is_array($parents['detail'])){
                    foreach($parents['detail'] as $cat){
                        if($cat['key'] == $eachCategory){
                            $rs['parents'] = array('id'=>$parentId , 
                                    'key'=> $parents['key'] , 
                                    'name'=> $parents['name'] ,
                            );
                            $dataLink->parents = $parents['key'];
                            $dataLink->cat = $cat['key'];
                            $rs['cat'] = array('id' => $cat['id'],
                                    'key' => $cat['key'],
                                    'name' => $cat['name'],
                                    'link'=>App_Common::getLink($dataLink),
                            );
                            break 2;
                        }
                    }
                }
            }
        }
        return $rs;
    }
    
}