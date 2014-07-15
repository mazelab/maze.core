<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Api
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Api
{
    
    /**
     * Singleton instance
     *
     * @var Core_Model_Module_Api
     */
    protected static $_instance = null;
    
    /**
     * Instance of Core_Model_Module_Api_Broker
     * 
     * @var Core_Model_Module_Api_Broker
     */
    protected $_modules = null;

    /**
     * init module broker
     */
    public function __construct()
    {
        $this->_modules = new Core_Model_Module_Api_Broker();
    }

    /**
     * Enforce singleton; disallow cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * trigger when module will be deinstalled
     *
     * @param string $service
     * @return boolean
     */
    public function deinstall($service)
    {
        if(!$this->hasModule($service) || !($module = $this->getModule($service))) {
            return true;
        }

        return $module->deinstall();
    }

    /**
     * returns all clients of a certain node which are set in a particular module
     * 
     * @param string $nodeId
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClientsByNode($nodeId, $moduleName = null)
    {
        return $this->_modules->getClientsByNode($nodeId, $moduleName);
    }
    
    /**
     * returns all domains which are set in a particular module
     * 
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomains($moduleName)
    {
        return $this->_modules->getDomains($moduleName);
    }
    
    /**
     * returns all domains of a certain client on a certain node
     * 
     * @param string $nodeId
     * @param string $clientId
     * @param strign $moduleName
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomainsByNode($nodeId, $clientId = null, $moduleName = null)
    {
        return $this->_modules->getDomainsByNode($nodeId, $clientId, $moduleName);
    }
    
    /**
     * Singleton instance
     *
     * @return Core_Model_Module_Api
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * returns all nodes which are set in a particular module
     * 
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodes($moduleName = null)
    {
        return $this->_modules->getNodes($moduleName);
    }
    
    /**
     * returns all nodes of a certain domain
     * 
     * @param string $domainId
     * @param string $moduleName name of the module
     * @param boolean $byModule adds moduleName as first key before result
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByDomain($domainId, $moduleName = null, $byModule = false)
    {
        return $this->_modules->getNodesByDomains($domainId, $moduleName, $byModule);
    }
    
    /**
     * returns all nodes of a certain client which are set in a particular module
     * 
     * @param string $clientId
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByClient($clientId, $moduleName = null)
    {
        return $this->_modules->getNodesByClient($clientId, $moduleName);
    }
    
    /**
     * Retrieve a module by moduleName
     *
     * @param  string $moduleName
     * @return Core_Model_Module_Api_Abstract|null
     */
    public function getModule($moduleName)
    {
        return $this->_modules->getModule($moduleName);
    }

    /**
     * Retrieve all modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules->getModules();
    }
    
    /**
     * Is a particular module registered?
     *
     * @param  string $moduleName
     * @return bool
     */
    public function hasModule($moduleName)
    {
        return $this->_modules->hasModule($moduleName);
    }

    /**
     * Register a module.
     *
     * @param string $moduleName name of the module
     * @param Core_Model_Module_Api_Abstract $moduleApi
     * @return Core_Model_Module_Api
     */
    public function registerModule($moduleName, Core_Model_Module_Api_Abstract $moduleApi)
    {
        $this->_modules->registerModule($moduleName, $moduleApi);
        return $this;
    }
    
    /**
     * trigger when client or client service will be removed
     *
     * @param  string $clientId
     * @param  string $service of a certain service
     * @return boolean
     */
    public function removeClient($clientId, $service = null)
    {
        $modules = array();
        if($service && $this->hasModule($service)) {
            $modules[$service] = $this->getModule($service);
        } else {
            $modules = $this->getModules();
        }

        foreach($modules as $module) {
            if(!$module->removeClient($clientId)) {
                return false;
            }
        }

        return true;
   }
   
    /**
     * trigger when client or client service will be removed
     *
     * @param  string $domainId
     * @param string $service of a certain service
     * @return boolean
     */
    public function removeDomain($domainId, $service = null)
    {
        $modules = array();
        if($service && $this->hasModule($service)) {
            $modules[$service] = $this->getModule($service);
        } else {
            $modules = $this->getModules();
        }

        foreach($modules as $module) {
            if(!$module->removeDomain($domainId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * trigger when node or node service will be removed
     *
     * @param  string $nodeId
     * @param  string $service of a certain service
     * @return boolean
     */
    public function removeNode($nodeId, $service = null)
    {
        $modules = array();
        if($service && $this->hasModule($service)) {
            $modules[$service] = $this->getModule($service);
        } else {
            $modules = $this->getModules();
        }

        foreach($modules as $module) {
            if(!$module->removeNode($nodeId)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * reports a certain node service
     * 
     * @param string $nodeId
     * @param string $service
     * @param string $report
     * @return boolean
     */
    public function reportNodeService($service, $nodeId, $report)
    {
        if(!$this->hasModule($service) || !($module = $this->getModule($service))) {
            return false;
        }
        
        return $module->reportNode($nodeId, $report);
    }
    
    /**
     * Unregister a module.
     *
     * @param  string $moduleName name of the module
     * @return Core_Model_Module_Api
     */
    public function unregisterModule($moduleName)
    {
        $this->_modules->unregisterModule($moduleName);
        return $this;
    }
    
    /**
     * returns the module domain validation
     * 
     * @param  string $service
     * @param  string $domainId
     * @return boolean
     */
    public function validateDomainForService($service, $domainId)
    {
        if(!$this->hasModule($service) || !($module = $this->getModule($service))) {
            return true;
        }

        return $module->validateDomainForService($domainId);
    }

    /**
     * triggers before adding a client service
     *
     * if returned false it will abort adding the service
     *
     * @param string $service
     * @param string $clientId
     * @return boolean
     */
    public function preAddClientService($service, $clientId)
    {
        if(!$this->hasModule($service) || !($module = $this->getModule($service))) {
            return true;
        }

        return $module->preAddClientService($clientId);
    }

    /**
     * triggers before removing a node service
     *
     * if returned false it will abort adding the service
     *
     * @param string $service
     * @param string $nodeId
     * @return boolean
     */
    public function preRemoveNodeService($service, $nodeId)
    {
        if(!$this->hasModule($service) || !($module = $this->getModule($service))) {
            return true;
        }

        return $module->preRemoveNodeService($nodeId);
    }
    
}
