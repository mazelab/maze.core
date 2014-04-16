<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Node
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Node extends Core_Model_ServiceObject
{
    
    /**
     * log action for node reports
     */
    CONST ACTION_REPORT_NODE_SERVICE = 'report node service';
    
    /**
     * message when saving failed
     */
    CONST ERROR_SAVING = 'Something went wrong while saving node %1$s';

    /**
     * message for reporting a node service
     */
    CONST MESSAGE_REPORT_NODE_SERVICE = 'Report of service %1$s from node %2$s - %3$s';
    
    /**
     * @var Core_Model_Node_Commands
     */
    protected $_commands;
    
    /**
     * flag to determine if search index should be rebuild after save operation
     * 
     * @var boolean
     */
    protected $_rebuildSearchIndex;
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Node
     */
    public function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getNode();
    }

    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getNode($this->getId());
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveNode($unmappedContext, $this->getId());
        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getName());
            return false;
        }
        
        $this->_setId($id);
        if($this->_rebuildSearchIndex) {
            $this->_rebuildSearchIndex = false;
            Core_Model_DiFactory::getIndexManager()->setNode($id);
        }

        return $id;
    }
        
    /**
     * deletes this node in data backend
     * 
     * @return boolean
     */
    public function delete()
    {
        if (!$this->getId()) {
            return false;
        }
        
        if(!$this->_getProvider()->deleteNode($this->getId())) {
            return false;
        }
        
        Core_Model_DiFactory::getIndexManager()->unsetNode($this->getId());
        
        return true;
    }

    /**
     * alias of getData('apiKey')
     * 
     * @return string
     */
    public function getApiKey()
    {
        return $this->getData('apiKey');
    }
    
    /**
     * gets commands object
     * 
     * @return Core_Model_Node_Commands
     */
    public function getCommands()
    {
        if (!$this->_commands) {
            $this->_commands = Core_Model_DiFactory::newNodeCommand($this->getId());
        }
        
        return $this->_commands;
    }
    
    /**
     * return ip address from data set
     * 
     * @return string
     */
    public function getIp()
    {
        return $this->getData('ipAddress');
    }
    
    /**
     * returns node name from data set
     * 
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
    
    /**
     * get node commands of a certain service
     * 
     * @param string $service
     * @return array
     */
    public function getServiceCommands($service)
    {
        return $this->getCommands()->getCommands($service);
    }
    
    /**
     * get hash of last service report
     * 
     * @param string $service
     * @return string|null
     */
    public function getServiceReportHash($service)
    {
        if(!$this->hasService($service) || 
                !($hash = $this->getData("services/${service}/report"))) {
            return null;
        }
        
        return $hash;
    }
    
    /**
     * reports to a certain service of this node
     * 
     * @param string $service
     * @param string $report
     * @return boolean
     */
    public function reportService($service, $report)
    {
        if(!$this->hasService($service)) {
            return false;
        }
        
        $reportHash = md5($report);

        if (mb_detect_encoding($report, "UTF-8", true) != "UTF-8") {
            $report = utf8_encode($report);
        }

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
            ->setMessage(self::MESSAGE_REPORT_NODE_SERVICE)
            ->setMessageVars($service, $this->getName(), $this->getIp())
            ->setData(array('report' => $report, 'hash' => $reportHash))
            ->setAction(self::ACTION_REPORT_NODE_SERVICE)->setModuleRef($service)
            ->setNodeRef($this->getId())->saveByContext($reportHash);
        
        $this->getCommands()->removeCommands($service);
        
        if(!Core_Model_DiFactory::getModuleApi()
                ->reportNodeService($service, $this->getId(), $report)){
            return false;
        }
        
        $this->setData(array("services/${service}/report" => $reportHash));
        
        return $this->save();
    }

    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return Core_Model_ValueObject_Domain
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (key_exists('name', $data) || key_exists('ipAddress', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return $this;
    }

}
