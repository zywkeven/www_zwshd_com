<?php
/**
 * 通过url获取参数，
 * @author Keven.Zhong
 * @Version 1.0 At 2014-06-30
 */
class App_Parameter {
    
    //前端是否读
    public static $appCacheRead = true;
    //前端是否写
    public static $appCacheWrite = true;
    //api是否开启读缓存
    public static $apiCacheRead = true;
    //api是否开启写缓存
    public static $apiCacheWrite = true;
    
    private static $authToken = '1';
   
    public static function getCacehState(){
        if(isset($GLOBALS['enableCache'])){
            $enableCache = $GLOBALS['enableCache'];
        } else {
            $enableCache = true;
        }
        if ($enableCache){
            if (!empty($_GET['preview'])) {
                $authToken = self::$authToken;
                $token = !empty($_GET['preview']) ? trim($_GET['preview']) : '';
                if ($token === $authToken) {
                    //预览模式
                    self::$appCacheRead = false;
                    self::$appCacheWrite = false;
                    self::$apiCacheRead = false;
                    self::$apiCacheWrite = false;
                }
            }
            $refresh = !empty($_GET['refresh']) ? trim($_GET['refresh']) : '';
            if($refresh == '5ebeb6065f64f2346dbb00ab789cf001'){
                if(isset($_GET['appCacheRead']) ){
                    if($_GET['appCacheRead'] == 0){
                        self::$appCacheRead = false;
                    } else {
                        self::$appCacheRead = true;
                    }
                }
                if(isset($_GET['appCacheWrite']) ){
                    if($_GET['appCacheWrite'] == 0) {
                        self::$appCacheWrite = false;
                    } else {
                        self::$appCacheWrite = true;
                    }
                }
                if(isset($_GET['apiCacheRead']) ){
                    if($_GET['apiCacheRead'] == 0) {
                        self::$apiCacheRead = false;
                    } else {
                        self::$apiCacheRead = true;
                    }
                }
                if(isset($_GET['apiCacheWrite'])){
                    if($_GET['apiCacheWrite'] == 0){
                        self::$apiCacheWrite = false;
                    } else {
                        self::$apiCacheWrite = true;
                    }
                }
            }
        } else {
            self::$appCacheRead = false;
            self::$appCacheWrite = false;
            self::$apiCacheRead = false;
            self::$apiCacheWrite = false;
        }
        $GLOBALS['enableCache'] = $enableCache;        
        
       
    }
}
