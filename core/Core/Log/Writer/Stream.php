<?php

class Core_Log_Writer_Stream extends Core_Log_Writer_AbstractWriter {
	
	/**
	 * stream句柄
	 *
	 * @var resource
	 */
	private $_handle = null;
	public static $file = null;
	
	public function __construct() {
		if (empty(self::$file)) {
			throw new Core_Exception_ResourceException(1003);
		}
		
		$dirName = dirname(self::$file);
		if (!is_dir($dirName)) {
			mkdir($dirName, 0755, true);
		}
		
		if (!$this->_handle = @fopen(self::$file, 'a', false)) {
			throw new Core_Exception_ResourceException(1002);
		}
		try {
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
		if (empty($this->_handle) || true === is_resource($this->_handle)) {
			@fclose($this->_handle);
		}
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
		$events = $this->_formatter->format($events);
		@fwrite($this->_handle, $events);
		$this->shutDown();
	}
}