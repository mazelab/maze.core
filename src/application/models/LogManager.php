<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_LogManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_LogManager
{
    
    /**
     * converts logged datetime in the given entries into local datetime
     * 
     * @todo dependancy Zend_Locale from application.ini
     * 
     * @param array $entries
     * @return array
     */
    protected function _convertLogDateTime(array $entries)
    {
        if(array_key_exists(Core_Model_Logger::KEY_DATETIME, $entries)) {
            $date = new Zend_Date($entries[Core_Model_Logger::KEY_DATETIME]);
            $entries[Core_Model_Logger::KEY_DATETIME] = $date->get(Zend_Date::DATETIME_SHORT);
        } else {
            foreach($entries as $entryId => $entry) {
                if(isset($entry[Core_Model_Logger::KEY_DATETIME])) {
                    $date = new Zend_Date($entry[Core_Model_Logger::KEY_DATETIME]);
                    $entries[$entryId][Core_Model_Logger::KEY_DATETIME] 
                            = $date->get(Zend_Date::DATETIME_SHORT);
                }
            }
        }
        
       return $entries; 
    }
    
    /**
     * returns logged conflicts
     * 
     * @param int $count
     * @return array
     */
    public function getAggregatedConflicts($count = 10)
    {
        return $this->getProvider()->getAggregatedConflicts($count);
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
        $log = $this->getProvider()->getClientContextLogs($clientId, $type, $action, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * returns the last client logs from data backend
     * 
     * @param string $clientId
     * @param int $count
     * @return array
     */
    public function getClientLogs($clientId, $count = 10)
    {
        $entries = $this->getProvider()->getClientLogs($clientId, $count);
        if(empty($entries) || !is_array($entries)) {
            return array();
        }
        
        return $this->_convertLogDateTime($entries);
    }
    
    /**
     * returns logged conflicts
     * 
     * @param int $count
     * @return array
     */
    public function getConflicts($count = 10)
    {
        return $this->getContextLogs(Core_Model_Logger::TYPE_CONFLICT, null, $count);
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
        $entry = $this->getProvider()->getContextLog($contextId, $type, $action);
        if(empty($entry) || !is_array($entry)) {
            return array();
        }
        
        return $this->_convertLogDateTime($entry);
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
        $entry = $this->getProvider()->getContextLogs($type, $action, $count);
        if(empty($entry) || !is_array($entry)) {
            return array();
        }
        
        return $this->_convertLogDateTime($entry);
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
        $log = $this->getProvider()->getDomainContextLogs($domainId, $type, $action, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * returns the last domain logs from data backend
     * 
     * @param string $domainId
     * @param int $count
     * @return array
     */
    public function getDomainLogs($domainId, $count = 10)
    {
        $log = $this->getProvider()->getDomainLogs($domainId, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * returns logged errors
     * 
     * @param int $count
     * @return array
     */
    public function getErrors($count = 10)
    {
        return $this->getContextLogs(Core_Model_Logger::TYPE_ERROR, null, $count);
    }
    
    /**
     * returns the last message from log backend
     * 
     * @param int $count
     * @return type
     */
    public function getLogs($count = 10)
    {
        if(!($entries = $this->getProvider()->getLogs($count)) || !is_array($entries)) {
            return array();
        }
        
        return $this->_convertLogDateTime($entries);
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
        $log = $this->getProvider()->getNodeContextLogs($nodeId, $type, $action, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * returns the last node logs from data backend
     * 
     * @param string $nodeId
     * @param int $count
     * @return array
     */
    public function getNodeLogs($nodeId, $count = 10)
    {
        $log = $this->getProvider()->getNodeLogs($nodeId, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
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
        $log = $this->getProvider()->getModuleContextLogs($moduleName, $type, $action, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * returns the last module logs from data backend
     * 
     * @param string $moduleName
     * @param int $count
     * @return array
     */
    public function getModuleLogs($moduleName, $count = 10)
    {
        $log = $this->getProvider()->getModuleLogs($moduleName, $count);
        if(empty($log) || !is_array($log)) {
            return array();
        }
        
        return $this->_convertLogDateTime($log);
    }
    
    /**
     * @return Core_Model_Dataprovider_Interface_Log
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getLog();
    }
    
    /**
     * returns logged successes
     * 
     * @param int $count
     * @return array
     */
    public function getSuccesses($count = 10)
    {
        return $this->getContextLogs(Core_Model_Logger::TYPE_SUCCESS, null, $count);
    }
    
    /**
     * returns logged warnings
     * 
     * @param int $count
     * @return array
     */
    public function getWarnings($count = 10)
    {
        return $this->getContextLogs(Core_Model_Logger::TYPE_WARNING, null, $count);
    }

    /**
     * translates log message and returns it
     *
     * @param array $log
     * @return array
     */
    public function translateLog(array $log)
    {
        if(!$log || !is_array($log)) {
            return array();
        }

        if(array_key_exists('message', $log) && Zend_Registry::isRegistered('Zend_Translate')) {
            /*@var $translator Zend_Translate */
            $translator = Zend_Registry::get('Zend_Translate');
            /*@var $adapter Zend_Translate_Adapter */
            $adapter = $translator->getAdapter();

            if(array_key_exists('messageVars', $log)) {
                $log['translation'] = vsprintf($adapter->translate($log['message']), $log['messageVars']);
            } else {
                $log['translation'] = $adapter->translate($log['message']);
            }
        }

        return $log;
    }

}

