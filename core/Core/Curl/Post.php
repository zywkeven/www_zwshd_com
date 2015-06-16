<?php
/**
 * POST方式 curl
 *
 */
class Core_Curl_Post extends Core_Curl_AbstractCurl {
	
	/**
	 * 调用父构造方法
	 *
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 实现cURL主体的抽象方法
	 *
	 * @param array $para
	 * 
	 * @return void
	 */
	protected function _cUrl($para = array()) {
		curl_setopt($this->_ch, CURLOPT_POST, true);
		if (!empty($para['data'])) {
			curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $para['data']);
		}
	}
}