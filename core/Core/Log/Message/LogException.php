<?php
class Core_Log_Message_LogException extends Core_Log_Message_AbstractMessage
{
    private $_exception = null;

    public function __construct($ex)
    {
        if (is_subclass_of($ex, 'Exception') || get_class($ex)=='Exception') {
            $this->_exception = $ex;
        }
    }

    public function getFormatMsg() 
    {
        if (!empty($this->_exception)) {
            return $this->_exception->__toString();
        } else {
            return '';
        }
    }
}
