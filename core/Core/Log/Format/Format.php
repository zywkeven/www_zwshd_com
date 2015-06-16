<?php
interface Core_Log_Format_Format {
	
	/**
	 * 日志格式接口
	 *
	 * @param mix $events
	 */
	public function format($events);
	
}