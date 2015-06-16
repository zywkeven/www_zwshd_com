<?php

class Core_Log_Writer_Mq extends Core_Log_Writer_AbstractWriter {
	
	private $_mq = null;
	public static $config = null;
	
	public function __construct() {
		if (empty(self::$config)) {
			throw new Core_Exception_SystemException(1008);
		}
		try {
			$this->_mq = Core_Mq::factory('rabbitMq');
			$this->_formatter = new Core_Log_Format_Simple();
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
		$this->_mq->disConnect();
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
		try {
			$msg = $this->_formatter->format($events);
			
			$this->_mq->createExchange(MQ_EXCHANGE_LOG, array('type' => self::$config['type'], 'flags' => self::$config['flags']));
			
			//建立queue
			foreach (self::$config['queue'] as $queue) {
				$this->_mq->createQueue($queue);
			}
			
			$this->_mq->publishMessage($msg, array('routingKey' => self::$config['route_key']));
		} catch (Exception $e) {
			throw $e;
		}
	}
}