<?php
abstract class Core_Log_Writer_AbstractWriter {
	
	/**
	 * 执行哪种写模式
	 *
	 * @var object
	 */
	protected $_formatter = null;
	
	public function __destruct() {
		$this->shutDown();
	}
	
	/**
	 * 记录日志时的抽象方法
	 *
	 */
	abstract protected function write($event);
	
	/**
	 * 关闭的抽象方法
	 *
	 */
	abstract protected function shutDown();
	
	/**
	 * 当需要自定义格式化字串时需要调用该方法
	 *
	 * @param string $formatter
	 *
	 * @return object
	 */
	public function setFormatter($formatter) {
		$this->_formatter = $formatter;
		return $this;
	}
	
}