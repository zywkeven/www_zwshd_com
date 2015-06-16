<?php
/**
 * Warning:
 * 当Logger使用setEventItem时，并且使用了SimpleFormat方式，必须传递格式，否则该writter不知道怎么做
 *
 */
class Core_Log_Format_Simple implements Core_Log_Format_Format {
	
	/**
	 * 字符串日志格式
	 *
	 * @var string
	 */
	private $_format = null;
		
	public function __construct($format = null) {
		/**
		 * 默认日志格式
		 */
		if (null === $format) {
			$format = '[%time%] [%type%] %message%' . PHP_EOL;
		}
		
		if (false === is_string($format)) {
			throw new Core_Exception_SystemException(1006);
		}
		$this->_format = $format;
	}
	
	/**
	 * 输出格式化过的日志
	 *
	 * @param array $events
	 *
	 * @return string
	 */
	public function format($events) {
		$outPut = $this->_format;
		foreach ($events as $key => $value) {
			$outPut = str_replace('%' . $key . '%', $value, $outPut);
		}
		return $outPut;
	}
}