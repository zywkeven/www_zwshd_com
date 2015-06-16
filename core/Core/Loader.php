<?php
/**
 * Loader
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Loader{
    
    //单例对象
    protected static $_instance;
    
    //类base路径
    protected static $_basePath = array();
    
    /**
     * 获取单例
     * @return Core_Loader
     */
    public static function getInstance(){
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * 设置类存放路径
     * @param mix $basePath
     * @return Core_Loader
     */
    public static function setBasePath($basePath){
        if (is_array($basePath)) {
            self::$_basePath = $basePath;
        } elseif (is_string($basePath)) {
            self::$_basePath = array($basePath);
        }
        return self::getInstance();
    }    
    
    protected function __construct(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    /**
     * 自动加载类
     * @param string $class
     * @return boolean
     */
    public static function autoload($class){
        $loader = self::getInstance();
        if ($loader) {
            if ($loader->_autoload($class)) {
                return true;
            }
        }        
        return false;
    }
    
    /**
     * 引入类文件
     * @param string $class
     * @return boolean
     */
    public function _autoload($class){
        $classPath = $this->getClassPath($class);
        if (false !== $classPath) {
            return include $classPath;
        }
        return false;
    }
    
    /**
     * 遍历basePath找到指定文件
     * @param string $class
     * @return string|boolean
     */
    public function getClassPath($class){        
        foreach (self::$_basePath as $path) {
            $classPath = $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            if (self::isReadable($classPath)) {
                return $classPath;
            }
        }    
        return false;
    }
    
    /**
     * 文件是否可读
     * @param string $filename
     * @return boolean
     */
    public static function isReadable($filename){
        if (is_readable($filename)) {          
            return true;
        }
    
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'
                && preg_match('/^[a-z]:/i', $filename)
        ) {            
            return false;
        }
    
        foreach (self::explodeIncludePath() as $path) {
            if ($path == '.') {
                if (is_readable($filename)) {
                    return true;
                }
                continue;
            }
            $file = $path . DIRECTORY_SEPARATOR . $filename;
            if (is_readable($file)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 路径展开
     * @param string $path
     * @return multitype:
     */
    public static function explodeIncludePath($path = null){
        if (null === $path) {
            $path = get_include_path();
        }
    
        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }    
    
}
