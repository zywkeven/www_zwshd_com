<?php
/**
 * cURL抽象类
 *
 */
abstract class Core_Curl_AbstractCurl {

	/**
	 * cURL资源
	 *
	 * @var resource
	 */
	protected $_ch = null;

	/**
	 * URL地址
	 *
	 * @var string
	 */
	protected $_url = '';

	/**
	 * 是否启用SSL模式
	 *
	 * @var boolean
	 */
	protected $_ssl = false;

	/**
	 * 初始化cURL资源
	 *
	 */
	protected function __construct() {
		$this->_ch = curl_init();
	}

	/**
	 * cURL抽象方法，处理POST、GET、PUT(暂不提供)
	 *
	 * @param array $para
	 */
	abstract protected function _cUrl($para = array());

	/**
	 * 发送socket连接
	 *
	 * @param string $url
	 * @param array $para array('header', 'location', 'cookieFile', 'data', 'timeout')
	 * @param boolean $return
	 *
	 * @return mix [void|string]
	 */
	private function _socket($url, $para, $return) {
		$this->_setUrl($url);

		if (isset($para['header']) && is_array($para['header'])) {
			curl_setopt($this->_ch, CURLOPT_HEADER, true);
			curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $para['header']);
		} else {
			$para['header'] = false;
		}

        unset($para['header']);

        if (isset($para['timeout'])) {
			curl_setopt($this->_ch, CURLOPT_TIMEOUT, intval($para['timeout']));
        } else {
			curl_setopt($this->_ch, CURLOPT_TIMEOUT, 10);
        }
        unset($para['timeout']);

        if (isset($para['user_agent'])) {
			curl_setopt($this->_ch, CURLOPT_USERAGENT, $para['user_agent']);
        }
        unset($para['user_agent']);

        if (isset($para['referer'])) {
			curl_setopt($this->_ch, CURLOPT_REFERER, $para['referer']);
        }
        unset($para['referer']);
        
		if (isset($para['location'])) {
			$para['location'] = true;
		} else {
			$para['location'] = false;
		}
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, $para['location']);
		unset($para['location']);

		if (isset($para['cookieFile'])) {
			curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $para['cookieFile']);
		}
        unset($para['cookieFile']);

		/*
		 * exec执行结果是否保存到变量中
		 */
		if (true === $return) {
			curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
		}

		/*
		 * 是否启用SSL验证
		 */
		if (true === $this->_ssl) {
			curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, true);
		}

		/*
		 * 调用子类处理方法
		 */
		$this->_cUrl($para);
		$result = curl_exec($this->_ch);
		$apiStatus = curl_getinfo($this->_ch);

		if(isset($apiStatus['http_code']) && '2' == substr($apiStatus['http_code'], 0, 1)) {
			if (true === $return) {
				curl_close($this->_ch);
				return $result;
			}
		} elseif(isset($apiStatus['http_code']) && 404 == $apiStatus['http_code']) {
			curl_close($this->_ch);
			throw new Core_Exception_SystemException('1001');
		} elseif(isset($apiStatus['http_code'])) {
			curl_close($this->_ch);
			throw new Core_Exception_SystemException('1001');
		} else {
			curl_close($this->_ch);
			throw new Core_Exception_SystemException('1001');           
        }
		
	}

	/**
	 * 初始化URL
	 *
	 * @param string $url
	 *
	 * @return boolean [true成功 | false失败]
	 */
	private function _setUrl($url) {
		$this->_url = $url;
		/*
		 * 以下代码在PHP > 5.3有效
		 */
		if (false !== strstr($this->_url, 'https://', true)) {
			$this->_ssl = true;
		}
		return curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
	}

	/**************************公共接口***********************/

	/**
	 * 发起通信请求接口
	 *
	 * @param string $url
	 * @param array $para array('data'=>请求参数, 'header'=>请求头信息, 'location'=>location信息, 'cookieFile'=>cookie信息, 'timeout'=>超时时间, 'referer'=>Referer头信息, 'user_agent'=>User-Agent头信息)
	 * @param boolean $return
	 *
	 * @return string
	 */
	final public function socket($url, $para = array(), $return = true) {
		return $this->_socket($url, $para, $return);
	}
    
	/**
	 * 设置setopt
	 *
	 * @param string $type  例如'CURLOPT_HTTPHEADER', 'CURLOPT_URL'等等
     * @param string $data  
	 *
	 * @return boolean [true成功 | false失败]
	 */
	final public function setOpt($type, $data) {
		return curl_setopt($this->_ch, $type, $data);
	}       
}