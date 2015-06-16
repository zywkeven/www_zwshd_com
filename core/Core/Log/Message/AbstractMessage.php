<?php
abstract class Core_Log_Message_AbstractMessage
{
    protected $logLevel = null;

    public function setLogLevel($log_level)
    {
        $this->logLevel = $log_level;
    }

    public function getLogLevel()
    {
        return $this->logLevel;
    }

    public function convLog4Level()
    {
        if (!class_exists('LoggerLevel')) {
            return;
        }
        switch ($this->logLevel) {
        case Core_Log_Logger::DEBUG:
            $this->logLevel=LoggerLevel::getLevelDebug();
            break;
        case Core_Log_Logger::INFO:
            $this->logLevel=LoggerLevel::getLevelInfo();
            break;
        case Core_Log_Logger::WARNING:
            $this->logLevel=LoggerLevel::getLevelWarn();
            break;
        case Core_Log_Logger::ERROR:
            $this->logLevel=LoggerLevel::getLevelError();
            break;
        case Core_Log_Logger::CRITICAL:
            $this->logLevel=LoggerLevel::getLevelFatal();
            break;
        default:
            $this->logLevel=LoggerLevel::getLevelInfo();
        }
    }

    abstract public function getFormatMsg();
}

