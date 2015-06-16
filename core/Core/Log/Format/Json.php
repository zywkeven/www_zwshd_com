<?php

class Core_Log_Format_Json implements Core_Log_Format_Format {
	
	
	/**
	 * 输出格式化过的日志
	 *
	 * @param array $events
	 *
	 * @return string
	 */
	public function format($events) {
		$outPut = json_encode($events);
		return $outPut;
	}
}
