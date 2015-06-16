<?php
/**
 * 异常类
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
abstract class Core_Exception_AbstractException extends Exception {
    
    /**
     * 构造方法
     * @param int $code
     * @param string $message
     */
    public function __construct($code, $message = null) {
        if (empty($message)) {
            $message = $this->_codeList[$code];
        }       
        parent::__construct($message, $code);
    }
    
}