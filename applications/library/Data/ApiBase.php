<?php
/**
 * api公用参数
 * @author Keven.Zhong
 * @Version 1.0 At 2014-04-16
 */
class Data_ApiBase{
    
    //前端是否读
    public $appCacheRead = true;
    //前端是否写
    public $appCacheWrite = true;
    //api是否开启读缓存
    public $apiCacheRead = true;
    //api是否开启写缓存
    public $apiCacheWrite = true;
    //前端重写缓存时缓存增加的时间
    public $addCacheTime = 0;
    
    public function __construct() { 
        $this->appCacheRead = App_Parameter::$appCacheRead;
        $this->appCacheWrite = App_Parameter::$appCacheWrite;                   
        $this->apiCacheRead = App_Parameter::$apiCacheRead;                   
        $this->apiCacheWrite = App_Parameter::$apiCacheWrite;       
    }
    
    /**
     * 设置成预览模式
     */
    public function setPreview(){
        $this->appCacheRead = false;
        $this->appCacheWrite = false;
        $this->apiCacheRead = false;
        $this->apiCacheWrite = false;
    }
    
    /**
     * 设置成刷新缓存模式
     */
    public function setRefresh(){
        $this->appCacheRead = false;
        $this->apiCacheRead = false;
    }    
    
}
