<?php
/**
 * XML方式格式化日志格式
 *
 */
class Core_Log_Format_Xml implements Core_Log_Format_Format {
	
	/**
	 * xml节点映射数组
	 * 格式为：array('element1', 'element2', 'element3', 'element4');
	 * @var array
	 */
	private $_elementMap = null;
		
	public function __construct($elementMap = null) {
		$this->_elementMap = $elementMap;
	}
	
	/**
	 * 以xml形式处理日志格式
	 * events格式与elementMap对应
	 * @param array $events
	 *
	 * @return string
	 */
	public function format($events) {
		$xmlData = array();
		if (null === $this->_elementMap) {
			$xmlData = $events;
		} else {
			foreach ($this->_elementMap as $name => $value) {
				$xmlData[$value] = $events[$name];
			}
		}
		$xml = Xml::generate($xmlData);
		return $xml . PHP_EOL;
	}
}