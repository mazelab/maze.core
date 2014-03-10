<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MongoDb_Mongo
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MongoDb_Mongo
{
    CONST PREFIX_SEPERATOR = '_';
    
    /**
     * @var Mongo
     */
    protected $_db;
    /**
     * @var string
     */
    protected $_dbName;
    /**
     * @var string
     */
    protected $_collectionPrefix;

    /**
     * @param Zend_Config $config 
     */
    public function __construct(Zend_Config $config = null)
    {
        if(!$config)
            $config = Zend_Registry::getInstance()->get('config');

        if($config->mongodb){
            $this->setDbName($config->mongodb->database)
                 ->setCollectionPrefix($config->mongodb->collectionPrefix);
        }
    }

    /**
     * changes the given Data set into dot notation according to the $root key
     * 
     * @param string $root base tier of dot notation
     * @param string|array $value
     * @return array
     */
    protected function _valueToDotNotation($root, $value)
    { 
        $return = array();
        $seperator = '.';
        if(!is_string($root)) {
            return array();
        }
        if(is_string($value) || is_int($value) || is_bool($value)) {
            $return[$root] = $value;
        }
        if(is_array($value)) {
            foreach($value as $key => $val) {
                if(!is_array($val)) {
                    $keyString = "$root$seperator$key";
                    $return[$keyString] = $val;
                    continue;
                }

                foreach($this->_valueToDotNotation($key, $val) as $subKey => $subValue) {
                    $keyString = "$root$seperator$subKey";
                    $return[$keyString] = $subValue;
                }
            }
        }
        
        return $return;
    }

    public function resetDatabase()
    {
        $this->_collectionPrefix = $this->_dbName = $this->_db = null;

        return $this;
    }

    /**
     * checks mongo db connection and returns the connection status
     * 
     * @return boolean
     */
    public function check()
    {
        if(!$this->getDbName())
            return false;        

        try {
            new Mongo;
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }
    
    /**
     * sets prefix for the collections
     * 
     * @param  string $prefix
     * @return MongoDb_Mongo
     */
    public function setCollectionPrefix($prefix)
    {
        $this->_collectionPrefix = $prefix;

        return $this;
    }

    /**
     * get collections prefix
     * 
     * @return null|string
     */
    public function getCollectionPrefix()
    {
        return $this->_collectionPrefix;
    }

    /**
     * set database name
     * 
     * @param  string $name
     * @return MongoDb_Mongo
     */
    public function setDbName($name)
    {
        $this->resetDatabase();
        $this->_dbName = $name;

        return $this;
    }

    /**
     * get the database name
     * 
     * @return null|string
     */
    public function getDbName()
    {
        return $this->_dbName;
    }

    /**
     * 
     * @param MongoDB $db
     * @return MongoDb_Mongo
     */
    public function setDatabase(MongoDB $db)
    {
        $this->_db = $db;

        return $this;
    }
    
    /**
     * @return Mongo
     */
    public function getDatabase()
    {
        if (!$this->_db && $this->check()){
            $database = $this->getDbName();
            $mongo = new Mongo;
            $this->setDatabase($mongo->$database);
        }

        return $this->_db;
    }

    /**
     * @param string $collectionName 
     * @return MongoCollection
     */
    public function getCollection($collection)
    {
        if($this->getCollectionPrefix($collection) == null) {
            $collectionName = $collection;
        } else {
            $collectionName = $this->getCollectionPrefix($collection);
            $collectionName .= self::PREFIX_SEPERATOR;
            $collectionName .= $collection;
        }

        return $this->getDatabase()->$collectionName;
    }
    
    /**
     * changes the given data set into mongoDb appropriate update structure
     * 
     * @param array $array
     * @return array
     */
    public function prepareUpdateDataSet($array)
    {
        $output = array();
        
        foreach($array as $key => $value){
            foreach($this->_valueToDotNotation($key, $value) as $subKey => $subValue) {
                $output[$subKey] = $subValue;
            }
        } 
        
        return $output;
    }
    
}

