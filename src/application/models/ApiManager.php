<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ApiManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ApiManager
{
    
    /**
     * log action for corrupted api
     */
    CONST LOG_ACTION_CORRUPTED_API = 'corrupted api';
    
    /**
     * log action for registered api
     */
    CONST LOG_ACTION_REGISTERED_API = 'registered api';
    
    /**
     * log action for unregistered apis
     */
    CONST LOG_ACTION_UNREGISTERED_API = 'unregistered api';
    
    /**
     * message for corrupted api requests
     */
    CONST MESSAGE_CORRUPTED_API = 'Corrupted Api request from %1$s / %2$s. Api request will be ignored.';
    
    /**
     * message when api key is registered
     */
    CONST MESSAGE_REGISTERED_API = 'Api key from %1$s / %2$s was registered';
    
    /**
     * message for unregistered api
     */
    CONST MESSAGE_UNREGISTERED_API = 'Unregistered Api request from %1$s / %2$s';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * return the request of a certain unregistered api request
     * 
     * @param string $apiKey
     * @return array
     */
    public function getUnregisteredApiRequest($apiKey)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $request = $logManager->getContextLog($apiKey, Core_Model_Logger::TYPE_CONFLICT
                , self::LOG_ACTION_UNREGISTERED_API);
        
        if(empty($request) || !is_array($request)) {
            return array();
        }
        
        return $request;
    }
    
    /**
     * return all unregistered api requests
     * 
     * @return array
     */
    public function getUnregisteredApiRequests()
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $apiRequests = $logManager->getContextLogs(Core_Model_Logger::TYPE_CONFLICT
                , self::LOG_ACTION_UNREGISTERED_API, null);
        
        if(empty($apiRequests) || !is_array($apiRequests)) {
            return array();
        }
        
        return $apiRequests;
    }
    
    /**
     * logs unregistred api request into data backend
     * 
     * @param string $apiKey
     * @param Zend_Controller_Request_Abstract $request
     * @return boolean
     */
    public function logCorruptedApiRequest(Zend_Controller_Request_Abstract $request)
    {
        if(!($ip = $request->getHeader('X-Maze-Node-Ip')) ||
                !($name = $request->getHeader('X-Maze-Node-Name')) ||
                !($apiKey = $request->getHeader('X-Maze-Node-Api'))) {
            return false;
        }
        
        $data = array(
            'name' => $name,
            'ipAddress' => $ip,
            'apiKey' => $apiKey
        );
        
        if(($node = Core_Model_DiFactory::getNodeByApiKey($apiKey))) {
            $this->_getLogger()->setNodeRef($node->getId());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_ERROR)
                ->setAction(self::LOG_ACTION_CORRUPTED_API)
                ->setMessage(self::MESSAGE_CORRUPTED_API)
                ->setMessageVars($name, $ip)->setData($data);
        
        return $this->_getLogger()->saveByContext($name);
    }
    
    /**
     * logs unregistred api request into data backend
     * 
     * @param string $apiKey
     * @param Zend_Controller_Request_Abstract $request
     * @return boolean
     */
    public function logUnregistredApiRequest(Zend_Controller_Request_Abstract $request)
    {
        if(!($ip = $request->getHeader('X-Maze-Node-Ip')) ||
                !($name = $request->getHeader('X-Maze-Node-Name')) ||
                !($apiKey = $request->getHeader('X-Maze-Node-Api'))) {
            return false;
        }
        
        $data = array(
            'name' => $name,
            'ipAddress' => $ip,
            'apiKey' => $apiKey
        );
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_CONFLICT)
                ->setRoute(array($name), 'registerApi')->setData($data)
                ->setAction(self::LOG_ACTION_UNREGISTERED_API)
                ->setMessage(self::MESSAGE_UNREGISTERED_API)
                ->setMessageVars($name, $ip);
        
        return $this->_getLogger()->saveByContext($name);
    }
    
    /**
     * reports a service of a certain node
     * 
     * @param string $apiKey
     * @param string $service
     * @param boolean
     */
    public function reportNodeService($apiKey, $service)
    {
        if(!($node = Core_Model_DiFactory::getNodeManager()->getNodeByApiKey($apiKey)) ||
                !$node->hasService($service)) {
            return false;
        }
        
        $fileManager = Core_Model_DiFactory::getFileManager();
        if(!($reportFile = $fileManager->receiveHttpFileInfo("report", "tmp_name"))) {
            return false;
        }
        
        if(!$node->reportService($service, file_get_contents($reportFile))) {
            return false;
        }

        return true;
    }
    
    /**
     * sets log entry of unregistered api request to registered
     * 
     * @todo refactoring
     * 
     * @param string $nodeName
     * @param string $nodeIp
     * @return boolean
     */
    public function registerApiKey($nodeName, $nodeIp)
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $apiRequest = $logManager->getContextLog($nodeName, Core_Model_Logger::TYPE_CONFLICT
                , self::LOG_ACTION_UNREGISTERED_API);
        
        if(empty($apiRequest) || !is_array($apiRequest)) {
            return false;
        }

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_REGISTERED_API)
                ->setMessageVars($nodeName, $nodeIp)
                ->setAction(self::LOG_ACTION_REGISTERED_API);

        return $this->_getLogger()->saveByContext($nodeName,
                Core_Model_Logger::TYPE_CONFLICT, self::LOG_ACTION_UNREGISTERED_API);
    }
    
}
