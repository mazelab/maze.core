<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Log
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Log
{
    
    /**
     * returns logs of a certain context and client
     * 
     * @param string $clientId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getClientContextLogs($clientId, $type, $action = null, $count = 10);
    
    /**
     * gets client logs
     * 
     * @param string $clientId
     * @param int $count
     */
    public function getClientLogs($clientId, $count = 10);
    
    /**
     * gets aggregated conflicts
     * 
     * @param int $count
     */
    public function getAggregatedConflicts($count);
    
    /**
     * gets a certain context entry found by contextId, type and/or action
     * 
     * @param string $contextId
     * @param string $type
     * @param string $action default null
     * @return array
     */
    public function getContextLog($contextId, $type, $action = null);
    
    /**
     * gets certain context entries found type and/or action
     * 
     * @param string $type
     * @param string $action default null
     * @param int $count
     * @return array
     */
    public function getContextLogs($type, $action = null, $count = 10);
    
    /**
     * returns logs of a certain context and domain
     * 
     * @param string $domainId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getDomainContextLogs($domainId, $type, $action = null, $count = 10);
    
    /**
     * gets domain logs
     * 
     * @param string $domainId
     * @param int $count
     */
    public function getDomainLogs($domainId, $count = 10);
    
    /**
     * returns the last message from log backend
     * 
     * @param int $count
     * @return array
     */
    public function getLogs($count = 10);
    
    /**
     * returns logs of a certain context and node
     * 
     * @param string $nodeId
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getNodeContextLogs($nodeId, $type, $action = null, $count = 10);
    
    /**
     * gets node logs
     * 
     * @param string $nodeId
     * @param int $count
     */
    public function getNodeLogs($nodeId, $count = 10);
    
    /**
     * returns logs of a certain context and module
     * 
     * @param string $moduleName
     * @param string $type
     * @param string $action
     * @param int $count
     * @return array
     */
    public function getModuleContextLogs($moduleName, $type, $action = null, $count = 10);
    
    /**
     * gets module logs
     * 
     * @param string $moduleName
     * @param int $count
     */
    public function getModuleLogs($moduleName, $count = 10);
    
    /**
     * adds new entry with given data set
     * 
     * @param array $data
     * @return string log id
     */
    public function save(array $data);

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
    public function saveByContext($contextId, $type, $action, array $data);
    
    /**
     * updates a certain log entry
     * 
     * @param string $logId
     * @param array $data
     * @return boolean
     */
    public function update($logId, array $data);
    
}

