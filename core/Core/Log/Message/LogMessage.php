<?php
class Core_Log_Message_LogMessage extends Core_Log_Message_AbstractMessage
{
    private $_logEvents = array();

    public function __construct($message=null, $action=null, 
        $response=null, $time=null 
    ) {
        if (!empty($message)) {
            $logEvent = new Core_Log_Message_LogEvent($message, $action, $response, $time); 
            $this->addEventObj($logEvent);
        }
    }

    public function addEventObj($logEvent)
    {
        $this->_logEvents[]=$logEvent;
    }
    public function addEvent($eventMsg)
    {
        $this->_logEvents[]=new Core_Log_Message_LogEvent($eventMsg);
    }

    public function getEvents() 
    {
        return $this->_logEvents();
    }

    public function getFormatMsg() 
    {
        $str='';
        foreach ($this->_logEvents as $event) {
            $str.= (empty($str))?'':"\n";
            $str.=$event->message.';';
            $str.=(empty($event->action)) ? '' : 'Action:'.$event->action.';';
            $str.=(empty($event->response)) ? '' : 'Response:'.$event->response.';';
            $str.=(empty($event->time)) ? '' : 'Time:'.$event->time;
            $ex=$event->getException();
            if (!empty($ex)) {
                //$str.='Exception:'.$ex->getTraceAsString();
                $str.=$ex;
            }
        }
        return $str;
    }
}
