<?php
class Core_Log_Writer_OutputWriter extends Core_Log_Writer_AbstractWriter {
	
	public function __construct() {
		$this->_formatter = new Core_Log_Format_Simple();
	}
	
	/**
	 * 关闭
	 *
	 * @return void
	 */
	public function shutDown() {
		//empty!!
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
		$events = $this->_formatter->format($events);
		echo $this->_event;
	}
}