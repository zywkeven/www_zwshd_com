<?php

class Core_Log_Writer_RedisQueue extends Core_Log_Writer_AbstractWriter {
	
	private $_cache = null;
	public static $config = null;
	
	public function __construct() {
		if (empty(self::$config)) {
			throw new Core_Exception_SystemException(1009);
		}
		
		try {
			$this->_cache = Core_Cache::factory('redis');
            //if (isset(self::$config['formatter']) && !empty(self::$config['formatter'])){
			//    $this->_formatter = new self::$config['formatter']();
            //}else{
			    $this->_formatter = new Core_Log_Format_Simple();
            //}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * 关闭
	 *
	 * @return void
	 */
	public function shutDown() {
		$this->_cache->disConnect(self::$config['group_name']);
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
        try {
            if (isset(self::$config['format_msg']) && self::$config['format_msg']==false){
                $msg = $events;
            }else{
                $msg = $this->_formatter->format($events);
            }
            if (isset(self::$config['db'])) {
                //$this->_cache->selectDb(self::$config['group_name'], self::$config['db']);
                $this->_cache->rPushListWithDb(self::$config['group_name'], self::$config['queue_name'], $msg, self::$config['db']);
            } else {
                //Modified by Lam: lPush->rPush 20130117
                $this->_cache->rPushList(self::$config['group_name'], self::$config['queue_name'], $msg);
            }
        } catch (Exception $e) {
            throw $e;
        }

	}
}
