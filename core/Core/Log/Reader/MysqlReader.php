<?php

class Core_Log_Reader_MysqlReader extends Core_Log_Reader_AbstractReader
{
    private $_config = null;

    public function __construct($config)
    {
        $this->_config=$config;
        $mysqlConf=array('log_store'=>array(
            'master'=>array(
                'host'=>$config['host'],
                'username'=>$config['username'],
                'password'=>$config['password'],
                'database'=>$config['database']
                )
            )
        );
        Core_Db::$config=$mysqlConf;
    }
    
    public function getLogById($logId)
    {
        $mysqldb=Core_Db::factory('log_store', 'master');
        $select=$mysqldb->select()->from(
            'global_log', array('domain','log_id','program','type','message','log_date','log_time')
        )->where('log_id=?', $logId);

        $return=$mysqldb->fetchAll($select);
        return $return;
    }

    public function printConfig()
    {
        $confStr='Class:'.get_class($this)."\n";
        $confStr.=print_r($this->_config, true);
        return $confStr;
    }


}
