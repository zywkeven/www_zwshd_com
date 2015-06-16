<?php
class Core_Log_Writer_Tt extends Core_Log_Writer_AbstractWriter {
	
	/**
	 * TT实例化对象
	 *
	 * @var object
	 */
	protected $_ttObj = null;
	
	/**
	 * TT日志group
	 *
	 * @var mix
	 */
	protected $_ttGroup = null;
	
	/**
	 * 日志事件数组
	 *
	 * @var array
	 */
	protected $_event = array();
	
	public function __construct(CustomTt $ttObj, $group) {
		$this->_ttObj = $ttObj;
		$this->_ttGroup = $group;
		if (null === $this->_formatter) {
			$this->_formatter = new Core_Log_Format_Xml();
		}
	}
	
	/**
	 * 写入TT
	 *
	 * @exception Core_Exception_ResourceException
	 *
	 * @return void
	 */
	protected function _write() {
		$this->_event = $this->_formatter->format($this->_event);
		try {
			while (true) {
				if (($i > 5) ||  $this->_ttObj->setMem($this->_ttGroup, null, $this->_event)){
					break;
				}
				$i++;
			}
		} catch (Vipcore_Exception_ResourceException $e) {
			$e->debug();
			exit;
		}
	}
	
	/**
	 * 关闭
	 *
	 * @return void
	 */
	public function shutDown() {
		$this->_ttObj = null;
		$this->_ttGroup = null;
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