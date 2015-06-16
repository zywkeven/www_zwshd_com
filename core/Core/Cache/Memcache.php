<?php
/**
 * Memcache缓存部分
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Cache_Memcache implements Core_Cache_Interface {
	
	private $_connections = array();
	
	private $_config = null;
	
	const persistent = true;
	
	const weight = 10;
	
	const timeout = 1;
	
	const retryInterval = 15;
	
	private $_lockSuffix = '_lockSuffix';
	
	private $_bigDataSuffix = '_bigDataSuffix';
	
	private $_bigSize = 800000;
	
	public function __construct($config) {
		if (!is_array($config) || empty($config)) {
			return null;
		}		
		$this->_config = $config;
	}
	
	private function _connect($group) {
		if (empty($group)) {
			return null;
		}

		if (empty($this->_connections[$group])) {
			$m = new Memcache();
			//add servers
			$persistent = isset($this->_config[$group]['persistent']) && $this->_config[$group]['persistent'] ? $this->_config[$group]['persistent'] : self::persistent;
			$timeout = isset($this->_config[$group]['timeout']) && $this->_config[$group]['timeout'] ? $this->_config[$group]['timeout'] : self::timeout;
			$weight = isset($this->_config[$group]['weight']) && $this->_config[$group]['weight'] ? $this->_config[$group]['weight'] : self::weight;
			$retryInterval = isset($this->_config[$group]['retry_interval']) && $this->_config[$group]['retry_interval'] ? $this->_config[$group]['retry_interval'] : self::retryInterval;
			foreach ( $this->_config[$group]['server'] as $server ) {
				$m->addServer($server['host'], $server['port'], $persistent, $weight, $timeout, $retryInterval);
			}
			if ($this->_config[$group]['compress_data']) {
				$m->setCompressThreshold($this->_config[$group]['compress_min_size'], $this->_config[$group]['compress_level']);
			}
			$this->_connections[$group] = $m;
		}

		return $this->_connections[$group];
	}	
	
	public function add($key, $val, $flag = false, $expire = 1800 , $group = 'default') {
		return $this->_connect($group)->add($key, $val, $flag, $expire);
	}
    
	public function get($key, $group = 'default') {
	    $rs = null;
	    $link = $this->_connect($group);
		$temValue = $link->get($key);
		if (isset($temValue['time']) && isset($temValue['val'])) {
		    if ($temValue['time'] < time()) {
		        if (true === $link->add($key . $this->_lockSuffix, '1', false, 60)) {//加锁
		            //加锁成功返回空数据，不需要处理
		        } else {
		            //加锁失败返回旧数据
		            $rs = $temValue['val'];
		        }
		    } else {
		        //数据未过期
		        $rs = $temValue['val'];
		    }
		}
		return $rs;
	}
	
	/**
	 * 切分大数据部分
	 * @param string $key
	 * @param string $val
	 * @param string $expire
	 * @param number $addExpire
	 * @param string $group
	 * @return boolean
	 */
	public function setBig($key, $val, $expire = null , $addExpire = 7200, $group = 'default') {
	    $expire = (isset($expire))? intval($expire) : $this->_config[$group]['expire'];
	    $addExpire = $this->addExpire($expire, $addExpire);
	    $jsonVal = json_encode($val);
	    $rs = str_split($jsonVal, $this->_bigSize);
	    $size = count($rs);
	    //第一个元素设置拆分大小及锁时间
	    $val = array(
	            'dataSize' => $size,
	            'val' => $rs[0],
	            'time' => time() + $expire,
	    );
	    $saveKey = $this->getBigDataSuffix($key, 0);
	    $return = $this->_connect($group)->set($saveKey, $val, false, $addExpire);
	    for($i = 1 ; $i< $size ; $i++){
	        $val = $rs[$i];
	        $saveKey = $this->getBigDataSuffix($key, $i);
	        $return = $this->_connect($group)->set($saveKey, $val, false, $addExpire);
	    }
	    return $return;
	}
	
	/**
	 * 获取切分数据后缀
	 * @param string $key
	 * @param string $num
	 * @return string
	 */
	private function getBigDataSuffix($key , $num = ''){
	    return $key .  $this->_bigDataSuffix . $num;
	}
	
	/**
	 * 获取切分数据
	 * @param string $key
	 * @param string $group
	 * @return Ambigous <NULL, mixed>
	 */
	public function getBig($key, $group = 'default') {
	    $rs = null;
	    $link = $this->_connect($group);
	    $startKey = $this->getBigDataSuffix($key, 0);
	    $temValue = $link->get($startKey);
	    if (isset($temValue['time']) && isset($temValue['val'])) {
	        if ($temValue['time'] < time()) {
	            if (true === $link->add($key . $this->_lockSuffix, '1', false, 60)) {//加锁
	                //加锁成功返回空数据，不需要处理
	            } else {
	                //加锁失败返回旧数据
	                $rs = $this->combineBigData($temValue['val'], $key, $temValue['dataSize'], $group);
	            }
	        } else {
	            //数据未过期
	            $rs = $this->combineBigData($temValue['val'], $key, $temValue['dataSize'], $group);
	        }
	    }
	    return $rs;
	}
	
	/**
	 * 拼接大数据截取部分
	 * @param string $value
	 * @param string $key
	 * @param int $totalSize
	 * @param string $group
	 * @return mixed
	 */
	private function combineBigData(&$value, $key, $totalSize, $group = 'default'){
	    $rs = $value;
	    if($totalSize > 1){
	        $link = $this->_connect($group);
	        for($i = 1; $i < $totalSize; $i ++){
	            $saveKey = $this->getBigDataSuffix($key, $i);
	             $rs .= $link->get($saveKey);	           
	        }
	    }
	    $rs = json_decode($rs, true);	
	    return $rs;
	}
	
	/**
	 * key分开获取
	 * @param string $key
	 * @param string $suffix Key的后缀
	 * @param number $version 如果分布修改了，必须修改此值
	 * @param string $lock 原来get的参数
	 * @param string $addExpire 原来get的参数
	 */
	public function multiGet($key, $suffix = '', $version = 1, $lock = true, $addExpire = true){
	    $saveKey = $this->combineMultiKey($key, $suffix, $version);
	    return $this->get($saveKey);
	}
	
	public function set($key, $val, $expire = null , $addExpire = 7200, $group = 'default') {
		$expire = (isset($expire))? intval($expire) : $this->_config[$group]['expire'];
		$addExpire = $this->addExpire($expire, $addExpire);
		$val = array(
		        'val' => $val,
		        'time' => time() + $expire,
		);
		return $this->_connect($group)->set($key, $val, false, $addExpire);
	}
	
	/**
	 * key一起保存
	 * @param string $key
	 * @param string $val
	 * @param int $expire
	 * @param int $num
	 * @param string $version
	 * @param int $addExpire
	 * @return $rs
	 */
	public function multiSet($key, $val, $expire, $num = 1, $version = 1, $addExpire = 7200){
	    $rs = null;
	    if($num > 0 ){
	        for($i = 0 ;$i < $num;$i++){
	            $saveKey = $this->combineMultiKey($key, $i, $version);
	            $rs = $this->set($saveKey, $val, $expire, $addExpire);
	        }
	    }
	    return $rs;
	}
	
	/**
	 * 组合分qps存储
	 * @param string $key 原始key
	 * @param int $num 随机数
	 * @param string $version 加上版本号
	 * @return string
	 */
	public function combineMultiKey($key, $num, $version){
	    return $key . ':' . $num . ':' . $version;
	}
	
	private function addExpire($expire, $addExpire){
	    $expire += abs($addExpire);	//增加缓存时间
	    return $expire < 2592000 ? $expire : 2592000;
	}
	
	public function delete($key, $group = 'default') {
		return $this->_connect($group)->delete($key);
	}
	
	public function status($group) {
		return $this->_connect($group)->getExtendedStats();
	}
	
	/**
	 * 设置是否压缩和压缩比
	 * @param boolean $compression
	 * @return boolean
	 */
	public function setCompressThreshold ($compression=false, $group = 'default'){
	    return $this->_connect($group)->setCompressThreshold ($compression);
	}
	
	public function __destruct() {
		if (!empty($this->_connections)) {
			$this->_connections = array();
		}
	}
}