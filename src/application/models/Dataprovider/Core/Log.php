<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Log
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Log 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Log
{

    /**
     * collection name
     */
    CONST COLLECTION = 'log';
    
    /**
     * key name action
     */
    CONST KEY_ACTION = Core_Model_Logger::KEY_ACTION;
    
    /**
     * key name client id
     */
    CONST KEY_CLIENT_ID = 'client.id';
    
    /**
     * key name context id
     */
    CONST KEY_CONTEXTID = Core_Model_Logger::KEY_CONTEXTID;
    
    /**
     * key name datetime
     */
    CONST KEY_DATETIME = Core_Model_Logger::KEY_DATETIME;
    
    /**
     * key name domain id
     */
    CONST KEY_DOMAIN_ID = 'domain.id';
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name node id
     */
    CONST KEY_NODE_ID = 'node.id';
    
    /**
     * key name module name
     */
    CONST KEY_MODULE_NAME = 'module.name';
    
    /**
     * key name type
     */
    CONST KEY_TYPE = Core_Model_Logger::KEY_TYPE;
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_CLIENT_ID => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_DOMAIN_ID => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_NODE_ID => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_MODULE_NAME => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_TYPE => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_CLIENT_ID => 1,
            self::KEY_TYPE => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_DOMAIN_ID => 1,
            self::KEY_TYPE => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_NODE_ID => 1,
            self::KEY_TYPE => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_MODULE_NAME => 1,
            self::KEY_TYPE => 1
        ));
        
        $this->_getLogCollection()->ensureIndex(array(
            self::KEY_CONTEXTID => 1,
            self::KEY_TYPE => 1
        ));
    }
    
    /**
     * gets log collection
     * 
     * @return MongoCollection
     */
    protected function _getLogCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * gets aggregated conflicts
     * 
     * @param int $count
     */
    public function getAggregatedConflicts($count)
    {
        $conflicts = array();
        $query = array(
            self::KEY_TYPE => Core_Model_Logger::TYPE_CONFLICT
        );
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        $group = array(
            '_id' => '$' . self::KEY_CLIENT_ID,
            'count' => array('$sum' => 1),
            'plugin' => array('$first' => '$plugin'),
            'logs' => array(
                '$push' => array(
                    'client' => '$client',
                    'datetime' => '$datetime',
                    'message' => '$message',
                    'messageVars' => '$messageVars',
                    'plugin' => '$plugin',
                    'type' => '$type',
                    'user' => '$user',
                    'url' => '$url'
                )
            ),
            'client' => array('$first' => '$client')
        );
         
        $opts = array(
            array(
                '$match' => $query
            ),
            array(
                '$sort' => $sort
            ),
            array(
                '$group' => $group
            ),
            array(
                '$limit' => $count
            )
        );

        if(!($aggregate = $this->_getLogCollection()->aggregate($opts)) 
                || !$aggregate['ok'] || empty($aggregate['result'])) {
            return array();
        }

        foreach($aggregate["result"] as $result){
            if (array_key_exists("client", $result) && !empty($result["client"])){
                unset($result["logs"]);
                $conflicts[]  = $result;
            } else {
                if(!array_key_exists("logs", $result)){
                    continue;
                }
                
                foreach ($result["logs"] as $log){
                    $conflicts[]  = $log;
                }
            }
        }

        return $conflicts;
    }
    
    /**
     * returns logs of a certain context and client
     * 
     * @param string $clientId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getClientContextLogs($clientId, $type, $action = null, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_CLIENT_ID => $clientId,
            self::KEY_TYPE => $type
        );
        
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * gets client logs
     * 
     * @param string $clientId
     * @param int $count
     */
    public function getClientLogs($clientId, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_CLIENT_ID => (string) $clientId
        );
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * gets a certain context entry found by contextId, type and/or action
     * 
     * @param string $contextId
     * @param string $type
     * @param string $action default null
     * @return array
     */
    public function getContextLog($contextId, $type = null, $action = null)
    {
        $query = array(
            self::KEY_CONTEXTID => (string) $contextId,

        );

        if($type) {
            $query[self::KEY_TYPE] = (string) $type;
        }
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        if(!($contextEntry = $this->_getLogCollection()->findOne($query)) || empty($contextEntry)) {
            return array();
        }
        
        $contextEntry[self::KEY_ID] = (string) $contextEntry[self::KEY_ID];
        return $contextEntry;
    }
    
    /**
     * gets certain context entries found type and/or action
     * 
     * @param string $type
     * @param string $action default null
     * @param int $count
     * @return array
     */
    public function getContextLogs($type, $action = null, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_TYPE => $type
        );
        
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * returns logs of a certain context and domain
     * 
     * @param string $domainId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getDomainContextLogs($domainId, $type, $action = null, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_DOMAIN_ID => $domainId,
            self::KEY_TYPE => $type
        );
        
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * gets domain logs
     * 
     * @param string $domainId
     * @param int $count
     */
    public function getDomainLogs($domainId, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_DOMAIN_ID => (string) $domainId
        );
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * returns the last message from log backend
     * 
     * @param int $count
     * @return array
     */
    public function getLogs($count = 10)
    {
        $logs = array();
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find()->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }

        return $logs;
    }
    
    /**
     * returns logs of a certain context and node
     * 
     * @param string $nodeId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getNodeContextLogs($nodeId, $type, $action = null, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_NODE_ID => $nodeId,
            self::KEY_TYPE => $type
        );
        
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * gets node logs
     * 
     * @param string $nodeId
     * @param int $count
     */
    public function getNodeLogs($nodeId, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_NODE_ID => (string) $nodeId
        );
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * returns logs of a certain context and module
     * 
     * @param string $moduleName
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getModuleContextLogs($moduleName, $type, $action = null, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_MODULE_NAME => $moduleName,
            self::KEY_TYPE => $type
        );
        
        if($action) {
            $query[self::KEY_ACTION] = (string) $action;
        }
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * gets module logs
     * 
     * @param string $nodeId
     * @param int $count
     */
    public function getModuleLogs($moduleName, $count = 10)
    {
        $logs = array();
        $query = array(
            self::KEY_MODULE_NAME => $moduleName
        );
        
        $sort = array(
            self::KEY_DATETIME => -1
        );
        
        foreach($this->_getLogCollection()->find($query)->sort($sort)->limit($count) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $logs[$id] = $entry;
        }
        
        return $logs;
    }
    
    /**
     * adds new entry with given data set
     * 
     * @param array $data
     * @return string log id
     */
    public function save(array $data) {
        $options = array(
            "j" => true
        );
        
        if (!$this->_getLogCollection()->insert($data, $options)) {
            return false;
        }
        
        return (string) $data[self::KEY_ID];
    }
    
    /**
     * saves/updates an entry with a contextId and a certain action and type
     * this combination should be seen as a unique entry
     * 
     * @param string $contextId
     * @param string $type
     * @param string $action
     * @param array $data
     * @return boolean
     */
    public function saveByContext($contextId, $type, $action, array $data)
    {
        $index = array(
            self::KEY_CONTEXTID => $contextId,
            self::KEY_TYPE => $type,
            self::KEY_ACTION => $action
        );
        
        $dataSet = array(
            '$set' => $data
        );
        
        $options = array(
            'upsert' => true,
            'j' => true
        );
        
        return $this->_getLogCollection()->update($index, $dataSet, $options);
    }
    
    /**
     * updates a certain log entry
     * 
     * @param string $logId
     * @param array $data
     * @return boolean
     */
    public function update($logId, array $data)
    {
        if(array_key_exists(self::KEY_ID, $data)) {
            unset($data[self::KEY_ID]);
        }
        
        $query = array(
            self::KEY_ID => new MongoId($logId),
        );
        
        $dataSet = array(
            '$set' => $data
        );
        
        $options = array(
            'j' => true
        );
        
        return $this->_getLogCollection()->update($query, $dataSet, $options);
    }
    
}
