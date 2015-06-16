<?php
/**
 * cache接口部分
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
interface Core_Cache_Interface {
    
    /**
     * 设置缓存
     * 
     * @param string $key 关键字
     * @param mix $val 内容
     * @param int $expire 有效期
     */
    public function set($key, $val, $expire = null , $group = 'default');
    
    /**
     * 取得缓存内容
     * 
     * @param string $key
     */
    public function get($key, $group = 'default');
    
    /**
     * 删除缓存
     * 
     * @param unknown_type $key
     */
    public function delete($key, $group = 'default');

}