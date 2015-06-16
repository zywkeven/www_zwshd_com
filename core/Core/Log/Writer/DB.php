<?php

class Core_Log_Writer_DB extends Core_Log_Writer_AbstractWriter {
	
	public static $config = null;
	private $_db = null;
	
	public function __construct() {
	}
	
	/**
	 * 关闭
	 *
	 * @return void
	 */
	public function shutDown() {
		if (empty($this->_db)) {
			$this->_db->close();
		}
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
		$this->_db = Core_Db::factory(self::$config['group_name'], 'master');
		
		$insertData = array('time' => $events['time'],
							'priority' => $events['priority'],
							'ip' => $events['ip'],
							'type' => $events['type'],
							'message' => $events['message']);
		$task_id = $this->_db->insert(self::$config['table_name'], $insertData);
	}
}