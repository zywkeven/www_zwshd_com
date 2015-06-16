<?php
/**
 * redis存储
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Cache_Redis implements Core_Cache_Interface {
	
	private $_connections = array();
	
	private $_config = null;
	
	private static $_keyLockPrefix = 'CORE_REDIS_KEY_LOCK_';
	const DEFAULT_HASH_KEY = 'hash';
	private static $_initShardingNodesSet = false;
	//哈希一致性算法
	private $_flexihash = null;
	
	public function __construct($config) {
		if (!is_array($config) || empty($config)) {
			throw new Core_Exception_SystemException(1001);
		}
		
		$this->_config = $config;		
		
		if (!empty($config['lock_prefix'])) {
			self::$_keyLockPrefix = $config['lock_prefix'];
		}
		$this->_flexihash = new Core_Flexihash();
	}
	
	public function __destruct() {
		if (!empty($this->_connections)) {
			$this->_connections = array();
		}
	}
	
	/**
	 * 连接服务器
	 * 
	 * @param string $group 
	 * @param string $hashKey 分片key
	 * 
	 * 
	 * @return mixed 成功返回redis连接句柄，失败返回void
	 */
	private function _connect($group, $hashKey = DEFAULT_HASH_KEY) {
		if (empty($group)) {			
			return;
		}
		
		if (!class_exists('Redis')) {			
			return;
		}

		if ($this->_isClusterSetting($group)) {
			$index = $this->_getShardingServerNode($group, $hashKey);
			if(isset ($this->_config[$group][$index])) {
			    if (empty($this->_connections[$group][$index])) {
			        $redis = new Redis();
			        $server = $this->_config[$group][$index];			        
			        $result = $redis->connect($server['host'], $server['port'], $server['timeout']);
			        if (!$result) {
			            //连接失败，去掉错误节点
			            if($this->_flexihash->getTargetCount() > 0){
			                $this->_flexihash->removeTarget($index);
			            }
			            if($this->_flexihash->getTargetCount() > 0){
    			            //重新初始化				            		          
    			            $redis = $this->_connect($group, $hashKey);
			            } else{
			                $redis = null;
			            }			            			            
			        }
			        $this->_connections[$group][$index] = $redis;
			        if($redis) {	        			        
    			        if (isset($server['db'])) {
    			            $redis->select($server['db']);
    			        }
			        }
			    }
			    return $this->_connections[$group][$index];			   
			} else {
			    return ;
			}
		} else {
			if (empty($this->_connections[$group])) {
				$redis = new Redis();
				$result = $redis->connect($this->_config[$group]['host'], $this->_config[$group]['port'], $this->_config[$group]['timeout']);
				$this->_connections[$group] = ($result)? $redis : false;
				
				if (false === $this->_connections[$group]) {
					throw new Core_Exception_SystemException(1001);
					return;
				}
			}
			return $this->_connections[$group];
		}
	}
	
	/**
	 * 初始化分片配置
	 * 
	 * @param string $group
	 * 
	 * @return void
	 */
	private function _initShardingNodes($group) {
		if (self::$_initShardingNodesSet) {
			return;
		}
		foreach($this->_config[$group] as $keyRedis => $eachRedis) {
		    $this->_flexihash ->addTarget($keyRedis);
		}		
		self::$_initShardingNodesSet = true;
	}
	
	/**
	 * 获取分片节点
	 * 
	 * @param string $group
	 * @param string $hashKey 分片key
	 * 
	 * @return mixed 成功返回分片服务器配置，失败返回false
	 */
	private function _getShardingServerNode($group, $hashKey = DEFAULT_HASH_KEY) {
		if (empty($this->_config[$group])) {
			return false;
		}
		
		$this->_initShardingNodes($group);
		$index = intval($this->_flexihash->lookup($hashKey));
		
		return $index;		
	}	
	
	
	private function _isClusterSetting($group) {
		if (empty($group)) {
			throw new Core_Exception_SystemException(1001);
			return;
		}
		
		$keys = array_keys($this->_config[$group]);
		foreach ($keys as $key) {
			if (!is_numeric($key)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 关闭非集群连接
	 * 
	 * @param string $group
	 * 
	 * @return void
	 */
	public function disConnect($group) {
		if ($this->_isClusterSetting($group)) {
			foreach ($this->_connections[$group] as $conn) {
				if ($conn) {
					$conn->close();
				}
			}
		} else {
			if ($this->isConnected($group)) {
				$this->_connections[$group]->close();
			}
		}
	}
	
	/**
	 * 判断是否已连接
	 * 
	 * @param string $group
	 * @param string $key
	 * 
	 * @return boolean 已连接返回true，未连接返回false
	 */
	public function isConnected($group, $key = DEFAULT_HASH_KEY) {
		if ($this->_isClusterSetting($group)) {
			$server = $this->_getShardingServerNode($group, $key);
			if(!$server) {
				return false;
			}
			return ($this->_connections[$group][$server['key']])? true : false;
		} else {
			return ($this->_connections[$group])? true : false;
		}
	}
	
	/**
	 * 重新连接
	 * 
	 * @param string $group
	 * @param string $key
	 * 
	 * @return boolean 成功返回true，失败返回false
	 */
	public function reConnect($group, $key = DEFAULT_HASH_KEY) {
		return ($this->_connect($group, $key))? true : false;
	}
	
	public function get($key, $group = 'default') {
		$return = null;
		$link = $this->_connect($group, $key);
		if($link){
    		$value = json_decode($link->get($key), true);		
    		if (isset($value['t']) && isset($value['v'])) {
    			if ($value['t'] < time()) {
    				if (true === $this->_connect($group, self::$_keyLockPrefix.$key)->setnx(self::$_keyLockPrefix.$key, '1')) {
    					$this->setTimeout($group, self::$_keyLockPrefix.$key, 30);
    					//加锁成功返回空数据，不需要处理
    				} else {
    					//加锁失败返回旧数据
    					$return = $value['v'];
    				}
    			} else {
    				//数据未过期
    				$return = $value['v'];
    			}
    		}	
		}	
		return $return;
	}
	
	
	public function set($key, $value, $expire = null, $group = 'default') {
	    $return = null;
		if (!$expire) {
			$expire = 7200;
		}		
		$value = array('v' => $value, 't' => time() + $expire);
		$link = $this->_connect($group, $key);
		if($link) {
    		$return = $link->set($key, json_encode($value));
    		if ($return) {
    			$expire = (isset($expire))? intval($expire * 2) : 2592000;//默认30天超时
    			$this->setTimeout($group, $key, $expire);
    		}
		}
		return $return;
	}
	
	/**
	 * 删除一个key
	 *
	 * @param string $group
	 * @param string $key
	 *
	 * @return int 成功删除的数目
	 */
	public function delete($key, $group = 'default') {
		return $this->_connect($group, $key)->delete($key);
	}
	
	
	/**
	 * 设置自增ID
	 *
	 * @param string $group
	 * @param string $key
	 * @param int $increment 每次自增量，默认为每次自增1，大于1有效
	 *
	 * @return int 自增新值
	 */
	public function increment($group, $key, $increment = null) {
		if (true === empty($increment)) {
			return $this->_connect($group, $key)->incr($key);
		} else {
			return $this->_connect($group, $key)->incrBy($key, $increment);
		}
	}

	/**
	 * 设置自减ID
	 *
	 * @param string $group
	 * @param string $key
	 * @param int $decrement 每次自减量，默认为每次自减1，大于1有效
	 *
	 * @return int 自增新值
	 */
	public function decrement($group, $key, $decrement = null) {
		if (true === empty($decrement)) {
			return $this->_connect($group, $key)->decr($key);
		} else {
			return $this->_connect($group, $key)->decrBy($key, $decrement);
		}
	}
	
	/**
	 * 检测给定的key是否存在
	 *
	 * @param string $group
	 * @param string $keyword
	 *
	 * @return boolean true成功,false失败
	 */
	public function keyExists($group, $key) {
		return $this->_connect($group, $key)->exists($key);
	}
	
	/**
	 * 获取key名称，支持通配符?*与选择[char]
	 * 本方法不支持读取分片存储的数据
	 *
	 * @param string $group
	 * @param array $keys
	 *
	 * @return array
	 */
	public function getKeys($group, $pattern = '*') {
		return $this->_connect($group)->getKeys($pattern);
	}
	
	/**
	 * 设定key过期时间
	 * 
	 * @param string $group
	 * @param string $key
	 * @param int $expire
	 * 
	 * @return boolean true成功,false失败
	 */
	public function setTimeout($group, $key, $expire) {
		if (!is_int($expire)) return false;
		$redis = $this->_connect($group, $key);
		if ($redis) {
			return $redis->setTimeout($key, $expire);
		} else {
			return false;
		}
	}	
    
}