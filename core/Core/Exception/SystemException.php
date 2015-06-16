<?php
/**
 * 系统的一些异常，需要自定义异常信息内容
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */

class Core_Exception_SystemException extends Core_Exception_AbstractException {

    protected $_codeList = array(        
        1001 => '系统级别错误',
    );

    /**
     * 自定义异常信息
     *
     * @param string $message
     */
    public function __construct($code, $message = null) {
        parent::__construct($code, $message);
    }

}