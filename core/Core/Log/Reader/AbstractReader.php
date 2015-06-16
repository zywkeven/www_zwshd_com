<?php
abstract class Core_Log_Reader_AbstractReader
{
    protected $formater = null;

    abstract protected function getLogById($logId);
    abstract protected function printConfig();
    
    public function setFormatter($formatter)
    {
        $this->formatter=$formatter;
        return $this;
    }
}
