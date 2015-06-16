<?php
/**
 * Api 接口
 * @author Keven.Zhong
 * @Version 1.0 At 2013-10-09
 */
class Model_OneApi {
    
    private static $_instance = null; //实例对象     
   
    private $_url = ''; //请求接口地址url   
        
    private function __construct() {
        $this->_url = $GLOBALS['config']['app']['oneApiUrl'];
    }
        
    /**
     * 获取单例实例
     * @return Model_OneApi
     */    
    public static function getInstance() {
        if (self::$_instance ===null) {
            self::$_instance = new Model_OneApi();
        }
        return self::$_instance;
    }
    
   /**
    * 调用接口返回所需值
    * @param array $parameter 参数
    * @return mixed
    */
    public function getApi($parameter){
        $totalTimes = 2;
        if ($totalTimes < 1){
            //配置小于1时强制成1
            $totalTimes = 1;
        }
        $rs = null;
        while($totalTimes > 0){
            $rs = $this->getApiOnce($parameter);
            if($rs !== null){
                break;
            }
            $totalTimes -= 1;
        }
        return $rs;
    }
      
    /**
     * 调用一次接口返回所需值
     * @param array $parameter
     * @return mixed
     */
    public function getApiOnce( $parameter){
        //api请求超时时间
        $timeOut = 10;        
        $allUrl = $this->_url.'?' . http_build_query($parameter);
        try{
            $curl = new Core_Curl_Get();
            //请求数据
            $result = $curl->socket($this->_url,  array('data'=>$parameter,'timeout'=>$timeOut));
            if($result) {
                return $result;
            } else {
                App_Common::addLog('OneApi without return:'.$allUrl, 'error');                
                return null;
            }
            unset($curl);
        } catch (Exception $e){
            App_Common::addLog('OneApi Exception:'.$allUrl . '  '. $e->getMessage().'code:'.$e->getCode(), 'error');            
            return null;
        }
    }
    
    
}
