<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Log
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Log
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
    implements Core_Model_Dataprovider_Interface_Log
{
    
    CONST COLLECTION = 'log';
    
    /**
     * gets aggregated conflicts
     * 
     * @param int $count
     */
    public function getAggregatedConflicts($count)
    {
        return array();
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
        return array();
    }
    
    /**
     * gets client logs
     * 
     * @param string $clientId
     * @param int $count
     */
    public function getClientLogs($clientId, $count = 10)
    {
        return array();
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
        return array();
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
        return array();
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
        return array();
    }
    
    /**
     * gets domain logs
     * 
     * @param string $domainId
     * @param int $count
     */
    public function getDomainLogs($domainId, $count = 10)
    {
        return array();
    }
    
    /**
     * returns the last message from log backend
     * 
     * @param int $count
     * @return array
     */
    public function getLogs($count = 10)
    {
        return array();
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
        return array();
    }
    
    /**
     * gets node logs
     * 
     * @param string $nodeId
     * @param int $count
     */
    public function getNodeLogs($nodeId, $count = 10)
    {
        return array();
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
        return array();
    }
    
    /**
     * gets module logs
     * 
     * @param string $moduleName
     * @param int $count
     */
    public function getModuleLogs($moduleName, $count = 10)
    {
        return array();
    }
    
    /**
     * adds new entry with given data set
     * 
     * @param array $data
     * @return string log id
     */
    public function save(array $data)
    {
        if(!is_array($data) || empty($data)) {
            return false;
        }
        
        $backend = $this->_getCollection(self::COLLECTION);
        $id = $this->_generateId();
        
        $context[$id] = $data;
        $context[$id]['_id'] = $id;
        
        $backend[$id] = $context;
        $this->_setCollection(self::COLLECTION, $backend);
        
        return $id;
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
        $backend = $this->_getCollection(self::COLLECTION);
        
        if(array_key_exists($contextId, $backend)) {
            $context = $backend[$contextId];
        } else {
            $context = array();
            $context['_id'] = $this->_generateId();
        }
        
        $context = array_merge_recursive($context, $data);
        $context['type'] = $type;
        $context['action'] = $action;

        $backend[$context['_id']] = $context;
        
        $this->_setCollection(self::COLLECTION, $backend);
        
        return true;
    }
    
    /**
     * adds/updates an entry with a contextId and a certain action/type
     * 
     * @param string $contextId
     * @param array $data
     * @param string $type
     * @param string $action default null
     * @return boolean
     */
    public function saveContextEntry($contextId, array $data, $type, $action = null)
    {
        return false;
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
        $backend = $this->_getCollection(self::COLLECTION);
        
        if(!array_key_exists($logId, $backend)) {
            return false;
        }
        
        $context = array_merge_recursive($backend[$logId], $data);
        $backend[$logId] = $context;
        
        $this->_setCollection(self::COLLECTION, $backend);
        
        return true;
    }
    
}

