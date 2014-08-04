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
     * removes the module collection
     *
     * @return boolean
     */
    public function deinstall()
    {
        return true;
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
     * event before adding a new client
     *
     * if returned false it will abort adding the client
     *
     * @param array $data
     * @return boolean
     */
    public function preAddClient(array $data)
    {
        return true;
    }

    /**
     * triggers before adding a client service
     *
     * if returned false it will abort adding the service
     *
     * @param string $clientId
     * @return boolean
     */
    public function preAddClientService($clientId)
    {
        return true;
    }

    /**
     * event before adding a new domain
     *
     * if returned false it will abort adding the domain
     *
     * @param array $data
     * @return boolean
     */
    public function preAddDomain(array $data)
    {
        return true;
    }

    /**
     * triggers before adding a domain service
     *
     * if returned false it will abort adding the service
     *
     * @param string $domainId
     * @return boolean
     */
    public function preAddDomainService($domainId)
    {
        return true;
    }

    /**
     * event before adding a new node
     *
     * if returned false it will abort adding the node
     *
     * @param array $data
     * @return boolean
     */
    public function preAddNode(array $data)
    {
        return true;
    }

    /**
     * triggers before adding a node service
     *
     * if returned false it will abort adding the service
     *
     * @param string $nodeId
     * @return boolean
     */
    public function preAddNodeService($nodeId)
    {
        return true;
    }

    /**
     * triggers before removing a node service
     *
     * if returned false it will abort adding the service
     *
     * @param string $nodeId
     * @return boolean
     */
    public function preRemoveClientService($nodeId)
    {
        return true;
    }

    /**
     * triggers before removing a domain service
     *
     * if returned false it will abort adding the service
     *
     * @param string $domainId
     * @return boolean
     */
    public function preRemoveDomainService($domainId)
    {
        return true;
    }

    /**
     * triggers before removing a node service
     *
     * if returned false it will abort adding the service
     *
     * @param string $nodeId
     * @return boolean
     */
    public function preRemoveNodeService($nodeId)
    {
        return true;
    }

    /**
     * event after a new client has been added
     *
     * @param string $clientId
     */
    public function postAddClient($clientId)
    {
    }

    /**
     * event after a new domain has been added
     *
     * @param string $domainId
     */
    public function postAddDomain($domainId)
    {
    }

    /**
     * event after a new node has been added
     *
     * @param string $nodeId
     */
    public function postAddNode($nodeId)
    {
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
