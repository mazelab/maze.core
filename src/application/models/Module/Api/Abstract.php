<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Api_Abstract
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class Core_Model_Module_Api_Abstract
{

    /**
     * returns all domains which are set in a particular module
     * 
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomains()
    {
        return array();
    }
    
    /**
     * returns all domains of a certain client on a certain node
     * 
     * @param string $nodeId
     * @param string $clientId
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomainsByNode($nodeId, $clientId = null)
    {
        return array();
    }
    
    /**
     * returns all nodes
     * 
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodes()
    {
        return array();
    }
    
    /**
     * returns all nodes of a certain domain
     * 
     * @param string $domainId
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByDomain($domainId)
    {
        return array();
    }
    
    /**
     * returns all nodes of a certain client which are set in a particular module
     * 
     * @param string $clientId 
     * @return array contains Core_Model_ValueObject_Node
     */
    public function getNodesByClient($clientId)
    {
        return array();
    }

    /**
     * returns all clients of a certain node
     * 
     * @param string $nodeId
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClientsByNode($nodeId)
    {
        return array();
    }
    
    /**
     * trigger when client or client service will be removed
     * 
     * @param  string $clientId
     * @return boolean
     */
    public function removeClient($clientId)
    {
        return true;
    }
    
    /**
     * removes the module collection
     * 
     * @return boolean
     */
    public function deinstall()
    {
        return true;
    }
    
    /**
     * trigger when domain or domain service will be removed
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function removeDomain($domainId)
    {
        return true;
    }

    /**
     * trigger when node or node service will be removed
     *
     * @param  string $nodeId
     * @return boolean
     */
    public function removeNode($nodeId)
    {
        return true;
    }

    /**
     * process report of a certain node
     * 
     * if false will be returned then the report will be abort
     * 
     * @param string $nodeId
     * @param string $report
     * @return boolean
     */
    public function reportNode($nodeId, $report)
    {
        return false;
    }
    
    /**
     * returns the module validation
     * 
     * @param  string $domainId
     * @return boolean
     */
    public function validateDomainForService($domainId)
    {
        return true;
    }

}
