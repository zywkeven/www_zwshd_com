<?php
/**
 * 以Xml方式写入输出日志
 *
 */
class Core_Log_Writer_Xml extends Core_Log_Writer_AbstractWriter {
	
	/**
	 * Event数组
	 */
	protected $_event = array();
	
	public function __construct() {
		$this->_formatter = new Core_Log_Format_Xml();
	}
	
	/**
	 * 直接以xml形式输出
	 *
	 * @return void
	 */
	protected function _write() {
		$this->_event = $this->_formatter->format($this->_event);
		echo $this->_event;
	}
	
	/**
	 * 关闭
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
		$this->_event = $events;
		$this->_write();
	}
}