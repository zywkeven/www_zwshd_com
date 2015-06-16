<?php
class Core_Log_Logger {
	
	/**
	 * 日志开关
	 * 1 不记录4以上信息
	 * 0 记录全部
	 */
	const LOG_SWITCH = 0;
	
	/**
	 * Log设施基数及级别参考：RFC-3164定义<http://www.faqs.org/rfcs/rfc3164.html>
	 * 
	 * log priority = BASIC_*_NUMBER * BASIC_LOG_NUMBER + log level
	 */
	const BASIC_LOG_NUMBER = 8; 		//日志基数
	const BASIC_KERNEL_NUMBER = 0; 		//核心基数
	const BASIC_USER_NUMBER = 1; 		//用户级别基数
	const BASIC_MAIL_NUMBER = 2; 		//邮件基数
	const BASIC_SYSTEM_NUMBER = 3; 		//系统基数
	const BASIC_S_A_NUMBER = 4; 		//安全与授权基数
	const BASIC_CLOCK_NUMBER = 9; 		//定时任务基数
	const BASIC_LOCAL_NUMBER = 20; 		//本地或自定义LOG级别基数
	
	//log level
	const EMERGENCY = 0; 	//system is unusable					紧急：系统无法使用
	const ALERT = 1; 		//action must be taken immediately		警报：必须采取行动，立即执行
	const CRITICAL = 2; 	//critical conditions					关键：关键条件
	const ERROR = 3; 		//error conditions						错误：错误状态
	const WARNING = 4; 		//warning conditions					警告：警告状态
	const NOTICE = 5; 		//normal but significant condition		注意：正常状态且十分重要
	const INFO = 6; 		//informational messages				信息：信息性消息
	const DEBUG = 7; 		//debug-level messages					调试：调试讯息
	
	//writer adapter
	const WRITER_STREAM = 1;
	const WRITER_TT = 2;
	const WRITER_MQ = 3;
	const WRITER_DATABASE = 4;
	const WRITER_REDIS_QUEUE = 5;
	const WRITER_LOG4PHP = 6;

    //log config
    public static $config = null;
	
	/**
	 * log对象
	 *
	 * @var object
	 */
	static private $_instance = null;
	
	/**
	 * 日志名称
	 *
	 * @var string
	 */
	private $_logName = '';
	
	/**
	 * 来源
	 *
	 * @var string
	 */
	private $_logSource = '';
	
	/**
	 * 事件ID
	 *
	 * @var int
	 */
	private $_logEventId = 0;
	
	/**
	 * 级别
	 *
	 * @var int
	 */
	private $_logLevel = null;
	
	/**
	 * 用户
	 *
	 * @var string
	 */
	private $_logUser = '';
	
	/**
	 * 操作代码
	 *
	 * @var int 16bit
	 */
	private $_logCode = '';
	
	/**
	 * 记录时间
	 * unix_timestamp
	 * @var int
	 */
	private $_logTime = null;
	
	/**
	 * 任务类别
	 *
	 * @var string
	 */
	private $_logType = '';
	
	/**
	 * 关键字
	 *
	 * @var string
	 */
	private $_logKeyWord = '';
	
	/**
	 * 日志内容
	 *
	 * @var string
	 */
	private $_logMessage = '';
	
	/**
	 * 日志优先级
	 * 数值越小优先级越高
	 * @var int
	 */
	private $_logPriority = 0;
	
	/**
	 * 日志基数数字
	 * 
	 * @var array
	 */
	private $_logBasicNumAry = array();
	
	/**
	 * 日志级别数组
	 *
	 * @var array
	 */
	protected $_logLevelAry = array();
	
	/**
	 * 日志记录方式数组
	 *
	 * @var array
	 */
	protected $_writerAry = array();
	
	/**
	 * 额外日志级别数组
	 *
	 * @var array
	 */
	protected $_extraAry = array();
	
	/**
	 * 日志记录方式对象
	 *
	 * @var array
	 */
	protected $_logWriterAry = array();
	
	private function __construct($writer = null) {
		$ref = new ReflectionClass($this);
		$const = $ref->getConstants();
		$constAry = array();
		foreach ($const as $key => $value) {
			if (preg_match('/^BASIC_(?:\w+)_NUMBER$/i', $key)) {
				$constAry['basic'][$key] = $value;
				continue 1;
			}
			if (preg_match('/^WRITER_(?:\w+)$/i', $key)) {
				$constAry['writer'][$key] = $value;
				continue 1;
			}
			$constAry['level'][$key] = $value;
		}
		$this->_logLevelAry = array_flip($constAry['level']);
		$this->_logBasicNumAry = array_flip($constAry['basic']);
		$this->_writerAry = array_flip($constAry['writer']);
		
		if (null !== $writer) {
			$this->addWriter($writer);
		}
	}
	
	public function __destruct() {
		foreach ($this->_logWriterAry as $writer) {
			$writer->shutDown();
		}
	}
	
	/**
	 * 单例入口
	 *
	 * @return object
     * Modified by Lam: 2013-01-09 : WRITER_LOG4PHP
	 */
	public static function getInstance($adapter = self::WRITER_STREAM) {
		if (null === self::$_instance) {
			switch ($adapter) {
				case self::WRITER_MQ:
					self::$_instance = new Core_Log_Logger(new Core_Log_Writer_Mq());
					break;
				case self::WRITER_STREAM:
					self::$_instance = new Core_Log_Logger(new Core_Log_Writer_Stream());
					break;
				case self::WRITER_DATABASE:
					self::$_instance = new Core_Log_Logger(new Core_Log_Writer_DB());
					break;
				case self::WRITER_REDIS_QUEUE:
					self::$_instance = new Core_Log_Logger(new Core_Log_Writer_RedisQueue());
					break;
				case self::WRITER_LOG4PHP:
					self::$_instance = new Core_Log_Logger(new Core_Log_Writer_Log4php());
					break;
				case self::WRITER_TT:
					throw new Core_Exception_SystemException(1007);
					break;
				default:
					throw new Core_Exception_SystemException(1007);
					break;
			}
		}
		return self::$_instance;
	}
	
	/**
	 * 取得日志的优先级/级别
	 *
	 * @param int $basic
	 * @param int $propority
	 *
	 * @throws Core_Exception_SystemException
	 *
	 * @return void
	 */
	private function _getLogPriority($basic, $pripority) {
		if (false === isset($this->_logBasicNumAry[$basic])) {
			throw new Core_Exception_SystemException(1005);
		}
		$this->_logPriority = self::BASIC_LOG_NUMBER * $basic + $pripority;
	}
	
	/**
	 * 魔术方法
	 *
	 * @param string $method
	 * @param array $params
	 */
	public function __call($method, $params = array()) {
		$logLevel = strtoupper($method);
		/**
		 * key = 常量值， value = 常量
		 */
		if (($logLevel = array_search($logLevel, $this->_logLevelAry)) !== false) {
			if (true === empty($params[1])) {
				$params[1] = self::BASIC_LOCAL_NUMBER;
			}
			if (1 == self::LOG_SWITCH ) {
				if ($logLevel > self::WARNING) {
					return;
				}
			}
			$this->log($params[0], $logLevel, $params[1]);
		}
		return $this;
	}
	
	/**
	 * 增加记录方式
	 *
	 * @param object $writer
	 *
	 * @return object
	 */
	public function addWriter($writer) {
		$this->_logWriterAry[] = $writer;
		return $this;
	}
	
	/**
	 * 添加一个自定义日志级别
	 *
	 * @param string $name
	 * @param int $value
	 *
	 * @return object
	 */
	public function addPriority($name, $value) {
		/**
		 * 转为“常量”
		 */
		$name = strtoupper($name);

		if (isset($this->_logLevelAry[$value]) || array_search($name, $this->_logLevelAry)) {
			throw new Core_Exception_SystemException(1002);
		}
		
		$this->_logLevelAry[$value] = $name;
		return $this;
	}
	
	/**
	 * 设置日志事件
	 *
	 * @param string $name
	 * @param mix $value
	 *
	 * @return object
	 */
	public function setEventItem($name, $value) {
		$this->_extraAry = array_merge($this->_extraAry, array($name => $value));
		return $this;
	}
	
	/**
	 * 移除日志事件
	 *
	 * @param string $name
	 *
	 * @return object
	 */
	public function removeEventItem($name) {
		if (true === array_key_exists($name, $this->_extraAry)) {
			unset($this->_extraAry[$name]);
		}
		return $this;
	}
	
    //Modified by Lam: 2013/01/15 - use internal loglevel (redis log)
    public function debug($logMsg) {
        //$logMsg->setLog4Level(LoggerLevel::DEBUG);
        if (is_string($logMsg)) {
            $objLogMsg=new Core_Log_Message_LogMessage($logMsg);
            $objLogMsg->setLogLevel($this::DEBUG);
            $this->addLog($objLogMsg);
        } elseif (is_object($logMsg) && get_class($logMsg)=='Core_Log_Message_LogMessage') {
            $logMsg->setLogLevel($this::DEBUG);
            $this->addLog($logMsg);
        }
    }
    public function info($logMsg) {
        //$logMsg->setLog4Level(LoggerLevel::INFO);
        if (is_string($logMsg)) {
            $objLogMsg=new Core_Log_Message_LogMessage($logMsg);
            $objLogMsg->setLogLevel($this::INFO);
            $this->addLog($objLogMsg);
        } elseif (is_object($logMsg) && get_class($logMsg)=='Core_Log_Message_LogMessage') {
            $logMsg->setLogLevel($this::INFO);
            $this->addLog($logMsg);
        }
    }
    public function warn($logMsg) {
        //$logMsg->setLog4Level(LoggerLevel::WARN);
        if (is_string($logMsg)) {
            $objLogMsg=new Core_Log_Message_LogMessage($logMsg);
            $objLogMsg->setLogLevel($this::WARNING);
            $this->addLog($objLogMsg);
        } elseif (is_object($logMsg) && get_class($logMsg)=='Core_Log_Message_LogMessage') {
            $logMsg->setLogLevel($this::WARNING);
            $this->addLog($logMsg);
        }
    }
    public function error($logMsg) {
        //$logMsg->setLog4Level(LoggerLevel::ERROR);
        if (is_string($logMsg)) {
            $objLogMsg=new Core_Log_Message_LogMessage($logMsg);
            $objLogMsg->setLogLevel($this::ERROR);
            $this->addLog($objLogMsg);
        } elseif (is_object($logMsg) && get_class($logMsg)=='Core_Log_Message_LogMessage') {
            $logMsg->setLogLevel($this::ERROR);
            $this->addLog($logMsg);
        }
    }
    public function fatal($logMsg) {
        //$logMsg->setLog4Level(LoggerLevel::FATAL);
        if (is_string($logMsg)) {
            $objLogMsg=new Core_Log_Message_LogMessage($logMsg);
            $objLogMsg->setLogLevel($this::CRITICAL);
            $this->addLog($objLogMsg);
        } elseif (is_object($logMsg) && get_class($logMsg)=='Core_Log_Message_LogMessage') {
            $logMsg->setLogLevel($this::CRITICAL);
            $this->addLog($logMsg);
        }
    }

    //log4php
    private function addLog4php($logMsg)
    {
        foreach ($this->_logWriterAry as $writer) {
            if (get_class($writer)=='Core_Log_Writer_Log4php') {
                $writer->write($logMsg);
            }
        }
    }

    //use obj: LogMessage
    public function addLog($logMsg)
    {

        $msg=$logMsg->getFormatMsg();
        try {
            throw new Exception('log');
        } catch (Exception $e) {
            $stack=$e->getTrace();
            $firstCall=array_pop($stack);
            $filename=(empty($firstCall))?'':basename($firstCall['file']);
        }
        if (isset(self::$config['local_log_file']) && self::$config['local_log_file']==true) {
            $logMsg->convLog4Level();
            $this->addLog4php($logMsg);
        } else {
            $this->log($msg, $logMsg->getLogLevel(), 20, $filename);
        }
    }

	/**
	 * 记录日志
	 *
	 * @param string $message	日志内容
	 * @param int $type 日志级别
	 *
	 * @throws SystemException
	 *
	 * @return void
	 */
	public function log($message, $type, $basic = self::BASIC_LOCAL_NUMBER, $program=null, $log_id=null ) {
		if (true === empty($this->_logWriterAry)) {
			throw new Core_Exception_SystemException(1003);
		}
		
		if (false === isset($this->_logLevelAry[$type])) {
			throw new Core_Exception_SystemException(1004);
		}
		
		$this->_getLogPriority($basic, $type);
    
        /*
        if ($log_id==null){
            $log_id=$this->getLogId();
        }*/
        if (self::$config!=null && isset(self::$config['log_level']) && $type>self::$config['log_level']){
            return false;
        }
        

		//拼装需要记录的日志数组
		$event = array_merge(array(
								'time' => date('Y-m-d H:i:s'), 
								'priority' => $this->_logPriority,
								'ip' => $this->_getRealIp(),
                                'local_ip' => $_SERVER['SERVER_ADDR'],
                                'domain'=>self::$config['domain'],
								'type' => $type, 
								'message' => $message,
                                'log_id' => $log_id,
                                'program' => $program,
							), $this->_extraAry);
				
		//记录日志，如果有多个记录方式，则循环记录
		foreach ($this->_logWriterAry as $writer) {
			$writer->write($event);
		}
	}
	
	/**
     * 取得IP
     *
     * @return IP
     */
    private function _getRealIp () {
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

    public function getLogId($length=32){
        $string = '';
        $possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz012345678';
        $possible .= strtolower($possible);
    
    
        for($i=1;$i<$length;$i++) {
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            $string .= $char;
        }
    
        return $string;
    }
}
