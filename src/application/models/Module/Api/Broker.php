<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Api_Broker
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Api_Broker
{

    CONST EXCEPTION_MODULE_IMPLEMENTATION = 'Module %1$s has a invalid api implementation in class %2$s';
    
    /**
     * Array of instance of objects extending Core_Model_Module_Api_Abstract
     *
     * @var array
     */
    protected $_modules = array();

    /**
     * init api connection of modules
     * 
     * @throws Core_Model_Module_Api_Exception
     */
    public function __construct()
    {
        foreach(Core_Model_DiFactory::getModuleRegistry()->getModules() as $module) {
            if(!($classApiCore = $module->getModuleConfig('api/core'))) {
                continue;
            }

            try{
                if(!class_exists($classApiCore)) {
                    continue;
                }

                if(!($moduleApi = new $classApiCore()) instanceof Core_Model_Module_Api_Abstract) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                throw new Core_Model_Module_Api_Exception(
                    vsprintf(self::EXCEPTION_MODULE_IMPLEMENTATION, array($module->getName(), $classApiCore))
                );
            }
            
            $this->registerModule($module->getName(), $moduleApi);
        }
    }
    
    /**
     * Register a certain module.
     *
     * @param  string $moduleName name of the module
     * @param  Core_Model_Module_Api_Abstract $stackIndex
     * @return Core_Model_Module_Api_Broker
     */
    public function registerModule($moduleName, Core_Model_Module_Api_Abstract $moduleApi)
    {
        if($this->hasModule($moduleName)) {
            throw new Core_Model_Module_Api_Exception('Module already registered');
        }
        
        $this->_modules[$moduleName] = $moduleApi;
        
        return $this;
    }
    
    /**
     * Unregister a module.
     *
     * @param string|Core_Model_Module_Api_Abstract $module Module object or class name
     * @return Core_Model_Module_Api_Broker
     */
    public function unregisterModule($moduleName)
    {
        if(!$this->hasModule($moduleName)) {
            throw new Core_Model_Module_Api_Exception('Module never registered.');
        }

        unset($this->_modules[$moduleName]);
        
        return $this;
    }
    
    /**
     * Is a module of a particular class registered?
     *
     * @param  string $moduleName
     * @return bool
     */
    public function hasModule($moduleName)
    {
        return isset($this->_modules[$moduleName]);
    }

    /**
     * Retrieve a module
     *
     * @param  string $moduleName  name of the module
     * @return Core_Model_Module_Api_Abstract|null
     */
    public function getModule($moduleName)
    {
        if(!$this->hasModule($moduleName)) {
            return null;
        }
        
        return $this->_modules[$moduleName];
    }

    /**
     * Retrieve all modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * returns all domains which are set in a particular module
     * 
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomains($moduleName)
    {
        if(!$this->hasModule($moduleName)) {
            return array();
        }
        
        return $this->getModule($moduleName)->getDomains();
    }
    
    /**
     * returns all nodes of a certain domain
     * 
     * @param string $domainId
     * @param string $moduleName name of the module
     * @param boolean $byModule adds moduleName as first key before result
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByDomains($domainId, $moduleName = null, $byModule = false)
    {
        $nodes = array();
        
        if($moduleName) {
            if(!$this->hasModule($moduleName)) {
                return array();
            }

            return $this->getModule($moduleName)->getNodesByDomain($domainId);
        } else {
            foreach($this->getModules() as $moduleName => $module) {
                foreach($module->getNodesByDomain($domainId) as $id => $node) {
                    if(!array_key_exists($id, $nodes)) {
                        if($byModule) {
                            $nodes[$moduleName][$id] = $node;    
                        } else {
                            $nodes[$id] = $node;
                        }
                    }
                }
            }
        }
        
        return $nodes;
    }
    
    /**
     * returns all nodes which are set in a particular module
     * 
     * @param string $moduleName name of the module
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodes($moduleName = null)
    {
        $nodes = array();
        
        if($moduleName) {
            if(!$this->hasModule($moduleName)) {
                return array();
            }

            return $this->getModule($moduleName)->getNodes();
        } else {
            foreach($this->getModules() as $module) {
                foreach($module->getNodes() as $id => $node) {
                    if(!array_key_exists($id, $nodes)) {
                        $nodes[$id] = $node;
                    }
                }
            }
        }
        
        return $nodes;
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
        $nodes = array();
        
        if($moduleName) {
            if(!$this->hasModule($moduleName)) {
                return array();
            }

            return $this->getModule($moduleName)->getNodesByClient($clientId);
        } else {
            foreach($this->getModules() as $module) {
                foreach($module->getNodesByClient($clientId) as $id => $node) {
                    if(!array_key_exists($id, $nodes)) {
                        $nodes[$id] = $node;
                    }
                }
            }
        }
        
        return $nodes;
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
        $domains = array();
        
        if($moduleName) {
            if(!$this->hasModule($moduleName)) {
                return array();
            }

            return $this->getModule($moduleName)->getDomainsByNode($nodeId, $clientId);
        } else {
            foreach($this->getModules() as $module) {
                foreach($module->getDomainsByNode($nodeId, $clientId) as $id => $domain) {
                    if(!array_key_exists($id, $domains)) {
                        $domains[$id] = $domain;
                    }
                }
            }
        }
        
        return $domains;
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
        $clients = array();
        
        if($moduleName) {
            if(!$this->hasModule($moduleName)) {
                return array();
            }

            return $this->getModule($moduleName)->getClientsByNode($nodeId);
        } else {
            foreach($this->getModules() as $module) {
                foreach($module->getClientsByNode($nodeId) as $id => $client) {
                    if(!array_key_exists($id, $clients)) {
                        $clients[$id] = $client;
                    }
                }
            }
        }
        
        return $clients;
    }
    
}
