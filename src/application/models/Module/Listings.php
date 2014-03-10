<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Listings
 *
 * builds module dependent listings with usage of the module api
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Listings
{
    
    /**
     * returns clients data with its domain context dependancy of node and module
     * 
     * @param string $nodeId
     * @return array
     */
    public function getClientsWithDomainsByNode($nodeId)
    {
        $clients = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()->getClientsByNode($nodeId)
                as $clientId => $client) {
            $clients[$clientId] = $client->getData();
            
            foreach(Core_Model_DiFactory::getModuleApi()
                    ->getDomainsByNode($nodeId, $clientId) as $domainId => $domain) {
                $clients[$clientId]['domains'][$domainId] = $domain->getData();
            }
        }
        
        return $clients;
    }
    
    /**
     * returns domains data with its client context dependancy of node and module
     * 
     * @param string $nodeId
     * @return array
     */
    public function getDomainsWithClientsByNode($nodeId)
    {
        $domains = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()->getDomainsByNode($nodeId)
                as $domainId => $domain) {
            $domains[$domainId] = $domain->getData();

            if(!($client = $domain->getOwner())) {
                continue;
            }
            
            $domains[$domainId]['client'] = $client->getData();
        }
        
        return $domains;
    }
    
        /**
     * returns domains data with its node context dependancy of client and module
     * 
     * @param string $clientId
     * @return array
     */
    public function getDomainsWithNodesByClient($clientId)
    {
        $domains = array();
        
        foreach(Core_Model_DiFactory::getDomainManager()->getDomainsByOwner($clientId)
                as $domainId => $domain) {
            $domains[$domainId] = $domain->getData();
            
            foreach(Core_Model_DiFactory::getModuleApi()
                    ->getNodesByDomain($domainId) as $nodeId => $node) {
                $domains[$domainId]['nodes'][$nodeId] = $node->getData();
            }
        }
        
        return $domains;
    }
    
        /**
     * returns domains data with its node context dependancy of client and module
     * 
     * @param string $moduleName
     * @return array
     */
    public function getDomainsWithOwnerAndNodes($moduleName = null)
    {
        $domains = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()
                ->getDomains($moduleName) as $domainId => $domain) {
            $domains[$domainId] = $domain->getData();
            
            if(($owner = $domain->getOwner())) {
                $domains[$domainId]['owner'] = $owner->getData();
            }
            
            foreach(Core_Model_DiFactory::getModuleApi()
                    ->getNodesByDomain($domainId, $moduleName) as $nodeId => $node) {
                $domains[$domainId]['nodes'][$nodeId] = $node->getData();
            }
        }
        
        return $domains;
    }
    
    /**
     * returns nodes data with domain context dependancy of module
     * 
     * @param string $moduleName
     * @param boolean $simple true if the node value is sufficient
     * @return array
     */
    public function getNodesWithDomains($moduleName = null)
    {
        $nodes = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()
                ->getNodes($moduleName) as $nodeId => $node) {
            $nodes[$nodeId] = $node->getData();
            
            foreach(Core_Model_DiFactory::getModuleApi()
                    ->getDomainsByNode($nodeId, null, $moduleName)
                    as $domainId => $domain) {
                $nodes[$nodeId]['domains'][$domainId] = $domain->getData();
            }
        }
        
        return $nodes;
    }
    
        /**
     * returns nodes data with domain context dependancy of client and module
     * 
     * @param string $clientId
     * @param boolean $simple true if the node value is sufficient
     * @return array
     */
    public function getNodesWithDomainsByClient($clientId)
    {
        $nodes = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()
                ->getNodesByClient($clientId)
                as $nodeId => $node) {
            $nodes[$nodeId] = $node->getData();
            
            foreach(Core_Model_DiFactory::getModuleApi()
                    ->getDomainsByNode($nodeId, $clientId) as $domainId => $domain) {
                $nodes[$nodeId]['domains'][$domainId] = $domain->getData();
            }
        }
        
        return $nodes;
    }
    
        /**
     * returns nodes data with its module context
     * 
     * @param string $domainId
     * @return array
     */
    public function getNodesWithServicesByDomain($domainId)
    {
        $result = array();
        
        foreach(Core_Model_DiFactory::getModuleApi()
                ->getNodesByDomain($domainId, null, true) as $moduleName => $nodes) {
            $module = Core_Model_DiFactory::getModuleRegistry()->getModule($moduleName);
            
            foreach($nodes as $nodeId => $node) {
                if(!key_exists($nodeId, $result)) {
                    $result[$nodeId] = $node->getData();
                }
                
                if($module) {
                    $result[$nodeId]['usedModules'][$module->getName()] = $module->getData();
                }
            }
        }
        
        return $result;
    }
    
}
