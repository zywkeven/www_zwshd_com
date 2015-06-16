<?php
class Core_Log_Message_LogEvent
{
    public $message;
    public $action;
    public $response;
    public $time;
    private $_exception;

    public function __construct($message,$action=null,$response=null,$time=null)
    {
        $this->message=$message;
        $this->action=$action;
        $this->response=$response;
        $this->time=$time;
    }

    public function setException($ex) 
    {
        if (is_subclass_of($ex, 'Exception')) {
            $this->_exception = $ex;
        }
    }

    public function getException()
    {
        return $this->_exception;
    }
}
