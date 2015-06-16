<?php

class Core_Log_Reader_MongodbReader extends Core_Log_Reader_AbstractReader
{
    private $_config = null;
    private $_mongo = null;

    public function __construct($config)
    {
        $this->_config=$config;
        $mongoConf=array('log_store'=>array(
            'slave'=>array(
                'host'=>$config['host'],
                'username'=>$config['username'],
                'password'=>$config['password'],
                'database'=>$config['database']
                )
            )
        );
        Core_MongoDb::$config=$mongoConf;
    }
    
    public function getLogById($logId)
    {
        $this->_mongo=Core_MongoDb::factory('log_store', 'slave');
        $criteria=array('log_id'=>$logId);
        $order=null;
        $limit=null;
        $outputCols=array('log_id','program','type','message','log_date','log_time');
        $result=$this->_mongo->find('global_log', $criteria, $order, $limit, $outputCols);

        return $result;
    }

    public function printConfig()
    {
        $confStr='Class:'.get_class($this)."\n";
        //$confStr.='ReadPreference:'.print_r($this->_mongo->getReadPreference(),true)."\n";
        $confStr.=print_r($this->_config, true);
        return $confStr;
    }


}
