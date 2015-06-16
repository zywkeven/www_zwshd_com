<?php
class Core_Log_Writer_Log4php extends Core_Log_Writer_AbstractWriter
{
    public static $config=null;
    private $_objLogger=null;
    public function __construct() 
    {
        if (empty(self::$config)) {
            //throw new Core_Exception_SystemException(1009);
            return;
        }
        if (class_exists('Logger') && isset(self::$config['log4php_config_file']) && file_exists(self::$config['log4php_config_file'])) {             
            Logger::configure(self::$config['log4php_config_file']);
            $logger_name = str_replace('.', '_', self::$config['domain']);
            $logger_name = (empty($logger_name))?'other':$logger_name;
            $log4phpini=file_get_contents(self::$config['log4php_config_file'], null, null, 0, 20480);
            $pos = strpos($log4phpini, 'log4php.appender.'.$logger_name.'=');
            $logger_name = ($pos>0)?$logger_name:'other';
            $this->_objLogger = Logger::getLogger($logger_name);
        }
    }

    public function write($log) 
    {
    	if ($this->_objLogger==null) {
            return;
        }
        try {
            throw new Exception('log');
        } catch (Exception $e) {
            $saveMsg = $e->getTrace();
        	$firstCall=array_pop($saveMsg);
            $filename=(empty($firstCall))?'':basename($firstCall['file']);
        }
        if (is_object($log)) {
            $msg = $log->getFormatMsg();
            $level = LoggerLevel::toLevel($log->getLogLevel());
        } else {
            $msg = '';
        }
        $public_ip=$this->_getRealIp();
        $private_ip=(isset($_SERVER['SERVER_ADDR']))?$_SERVER['SERVER_ADDR']:'';
        $this->_objLogger->log($level, $public_ip.'|'.$private_ip.'|'.$filename.'|'.$msg);
    }

    private function _getRealIp ()
    {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }

        $realip = explode(',', $realip);
        return $realip[0];
    }
    public function shutDown() 
    {
    }
}
