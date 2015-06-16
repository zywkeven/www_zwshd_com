<?php
/**
 * 缓存部分
 * @author Keven.Zhong
 * @Version 1.0 At 2014-01-01
 */
class Core_Db_MyPdo extends PDO {

    private $enableError = true;
    //last query result
    public $result; 
    public $lastQuery;
    //数据库连接参数
    private $dbType;
    private $host;
    private $dbname;
    private $user;
    private $pass;

    public function __construct($host, $dbname, $user, $pass, $dbType = 'mysql') {        
        try {
            if ($dbType == 'oci') {
                $oracle_home_path = '';
                if (is_dir($oracle_home_path))
                    putenv("ORACLE_HOME=" . $oracle_home_path);
                parent::__construct("{$dbType}:dbname=//{$host}/{$dbname};charset=AL32UTF8", $user, $pass);
                $this->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            }elseif ($dbType == 'dblib') {
                parent::__construct("$dbType:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            } else {
                $this->dbType = $dbType;
                $this->host = $host;
                $this->dbname = $dbname;
                $this->user = $user;
                $this->pass = $pass;
                $this->_parentConnect();  
            }
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(), 'error');
        }
    }
    
    /**
     * 初始化好参数好连接pdo
     */
    private function _parentConnect(){
        $charset = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';");
        return parent::__construct($this->dbType .
                ':host='.$this->host.';dbname=' . $this->dbname, $this->user, $this->pass, $charset);
    }  

    private function bindParams($sth, $bindArrParams) {
        if (count($bindArrParams) > 0) {
            $key = array_keys($bindArrParams);
            if (!is_numeric($key[0]) && (substr($key[0], 0, 1) == ':')) {
                foreach ($bindArrParams as $keyParams => $valueParams) {
                    $sth->bindValue($keyParams, $valueParams);
                }
                $this->result = $sth->execute();
            } else {

                $this->result = $sth->execute($bindArrParams);
            }
        } else {
            $this->result = $sth->execute();
        }
        return $sth;
    }

    public function simplePrepare($query, $bindArrParams = array()) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);
            $sth->closeCursor();
            return resultData::set($this->result, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(array(), $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    }

    /**
     * 不关闭连接，用于事务处理
     * @param type $query
     * @param type $bindArrParams
     * @return \resultData
     *
     */
    public function simpleQueryPrepare($query, $bindArrParams = array(), $fetchResult = false) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);           
            if ($fetchResult) {
                $this->result = $sth->fetchAll(parent::FETCH_ASSOC);
            }
            return resultData::set($this->result, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(array(), $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    }

    //执行变更，返回修改行数
    public function changePrepare($query, $bindArrParams = array()) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);
            $rowCount = $sth->rowCount();
            $sth->closeCursor(); // sblocco stment
            return resultData::set($rowCount, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(-1, $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    } 

    public function insertPrepare($query, $bindArrParams = array()) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);
            $sth->closeCursor();
            return resultData::set($this->lastInsertId(), $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(-1, $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    }

    public function insertPrepare_noPrimaryKey($query, $bindArrParams = array()) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);
            $sth->closeCursor();
            return resultData::set(1, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(-1, $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    }

    /**
     * 调用存储过程
     * @param type $query
     * @param type $bindArrParams
     * @return \resultData
     */
    public function callPrepare($query, $bindArrParams = array(), $output = array(':oo' => 'oo', ':msg' => 'msg')) {
        try {
            $this->checkGoneAway();
            $sth = parent::prepare($query);
            if (count($bindArrParams) > 0) {
                $key = array_keys($bindArrParams);
                if (!is_numeric($key[0]) && (substr($key[0], 0, 1) == ':')) {
                    foreach ($bindArrParams as $keyParams => $valueParams) {
                        $sth->bindValue($keyParams, $valueParams);
                    }
                }
            }
            if ($output) {
                foreach ($output as $k => $v) {
                    $sth->bindParam($k, $$v, PDO::PARAM_INPUT_OUTPUT, 20000);
                }
            }
            $sth->execute();
            $sth->closeCursor();

            if ($output) {
                foreach ($output as $k => $v) {
                    $ret[$v] = $$v;
                }
            } else {
                $ret = true;
            }
            return resultData::set($ret, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage(),'error');
            $result = new resultData(-1, $query, $bindArrParams);
            $result->set_error($e);           
            return $result;
        }
    }

    public function allPrepare($query, $bindArrParams = array()) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);
            $this->result = $sth->fetchAll(parent::FETCH_ASSOC);
            if($this->outputSql()){
                echo $this->showQuery($query, $bindArrParams).'<br>';
            }
            $sth->closeCursor();
            return resultData::set($this->result, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            $showQuery = $this->showQuery($query,$bindArrParams);
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() .',sql:'.$showQuery,'error');      
            $result = new resultData(-1, $query, $bindArrParams);
            $result->set_error($e);

            return $result;
        }
    }

    public function rowPrepare($query, $bindArrParams = array()) {
        try {    
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $sth = parent::prepare($query);
            $sth = $this->bindParams($sth, $bindArrParams);          
            if($this->outputSql()){
                echo $this->showQuery($query, $bindArrParams).'<br>';
            }
            $this->result = $sth->fetch(parent::FETCH_ASSOC);
            $sth->closeCursor();
            return resultData::set($this->result, $this->lastQuery, $bindArrParams);
        } catch (PDOException $e) {
            $showQuery = $this->showQuery($query,$bindArrParams);
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() .',sql:'.$showQuery,'error');  
            $result = new resultData(array(), $query, $bindArrParams);
            $result->set_error($e);
            return $result;
        }
    }

    public function simpleQuery($query) {
        try {
            $this->checkGoneAway();
            $this->lastQuery = $query;
            $count = parent::exec($query);
            return $count;
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() ,'error');  
        }
    }

    public function lastInsertId($name = '') {
        try {
            $id = 0;
            $this->checkGoneAway();
            if (parent::getAttribute(PDO::ATTR_DRIVER_NAME) == 'dblib') {
                $sth = parent::prepare("SELECT @@IDENTITY AS ID");
                $sth->execute();
                $result = $sth->fetch();
                $sth->closeCursor();
                return $result;
            }


            $id = parent::lastInsertId($name);
            return $id;
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() . "\n", 'error');
        }
    }
    
    public function checkGoneAway(){
        try{
            $dbInfo = @$this->getAttribute(PDO::ATTR_SERVER_INFO );
        }catch(PDOException $e){
            $code =  $e->getCode();
            if($code == 'HY000'){
                $this->closeConnection();
                $this->_parentConnect();
            }
        }
    }

    public function closeConnection() {
        try {
            $this->dbh = null;
        } catch (PDOException $e) {
            App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() . "\n", 'error');
        }
    }

    public function displayError() {
        try {
            parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
        	App_Common::addLog(__FUNCTION__ . " Error!: " . $e->getMessage() . "\n", 'error');
        }
    }

    public function getErrorCode() {
        return parent::errorCode();
    }

    public function getErrorInfo() {
        return parent::errorInfo();
    }   

    public function showQuery($query, $params){
        $keys = array();
        $values = array();
       
        if(is_array($params) ){
            foreach ($params as $key=>$value){
                if (is_string($key) && stripos($key,':') !== false){
                    $keys[] = $key;
                }
                
                if(is_numeric($value)){
                    $values[] = intval($value);
                } else {
                    $values[] = "'".addslashes($value) ."'";
                }
            }
            $query = str_replace($keys, $values, $query);
        }
        $query = preg_replace("/[\r\n]+/", ' ', $query);
        return $query;
    }
    
    /**
     * 传入指定参数，打印sql进行调试
     */
    public function outputSql(){
        $rs = false;
        if(isset($_GET['showSql']) && ($_GET['showSql'] == 1)){
            $rs = true;
        }
        return $rs;
    }

}

class resultData {

    private $query = null;
    private $par = null;
    private $data = array();
    private $error = null;

    public function __construct($data, $query, $par) {
        $this->data = $data;
        $this->query = $query;
        $this->par = $par;
    }

    function __get($data) {
        return $this->$data;
    }

    public function get() {
        return $this->data;
    }

    public function debug() {
        echo "<pre>";
        print_r($this);
        echo "</pre>";
    }

    public function getQuery() {
        return $this->query;
    }

    public function count() {
        if (is_int($this->data))
            return $this->data;
        return count($this->data);
    }

    public function isEmpty() {
        return empty($this->data);
    }

    public function row($index = 0) {
        return $this->data[$index];
    }

    public static function set($data, $query, $par) {
        return new resultData($data, $query, $par);
    }

    public function set_error($e) {
        $this->error = $e;
    }

    public function resultId() {
        if (!is_array($this->data))
            return $this->data;
    }
    
    public function __toString() {
    	return "code:" . $this->data . ";sql:" . print_r($this->query, true) . ";par:" . print_r($this->par, true);
    }

}

class Info implements ArrayAccess {

    function offsetSet($key, $value) {
        if (array_key_exists($key, get_object_vars($this))) {
            $this->{$key} = $value;
        }
    }

    function offsetGet($key) {
        if (array_key_exists($key, get_object_vars($this))) {
            return $this->{$key};
        }
    }

    function offsetUnset($key) {
        if (array_key_exists($key, get_object_vars($this))) {
            unset($this->{$key});
        }
    }

    function offsetExists($offset) {
        return array_key_exists($offset, get_object_vars($this));
    }

}