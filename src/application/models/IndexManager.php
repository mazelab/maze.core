<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_IndexManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_IndexManager
{

    /**
     * search category string for clients
     */
    CONST SEARCH_CATEGORY_CLIENT = 'Client';
    
    /**
     * search category string for domains
     */
    CONST SEARCH_CATEGORY_DOMAIN = 'Domain';
    
    /**
     * search category image for domains
     */
    CONST SEARCH_CATEGORY_DOMAIN_IMG = '/img/dummy_domain_50.png';
    
    /**
     * search category string for nodes
     */
    CONST SEARCH_CATEGORY_NODE = 'Node';

    /**
     * search category image for nodes
     */
    CONST SEARCH_CATEGORY_NODE_IMG = '/img/dummy_node_50.png';
    
    /**
     * @var Zend_EventManager_EventCollection
     */
    protected $_events;
    
    /**
     * Zend_View_Helper_Url
     */
    protected  $_urlHelper;
    
    /**
     * get search index core model
     * 
     * @return Core_Model_Search_Index
     */
    public function _getSearchIndex()
    {
        return Core_Model_DiFactory::getSearchIndex();
    }
    
    /**
     * get zend url helper
     * 
     * @return Zend_View_Helper_Url
     */
    public function _getUrlHelper()
    {
        if(!$this->_urlHelper) {
            $this->_urlHelper = new Zend_View_Helper_Url();
        }
        
        return $this->_urlHelper;
    }
    
    /**
     * sets core indexes and triggers module setIndexes event
     */
    public function setIndexes() {
        $this->_getSearchIndex()->clearIndexes();

        $this->setCoreIndexes();
        
        $this->getEvents()->trigger(__FUNCTION__, $this);
    }
    
    /**
     * get zend event collection object of this class
     * 
     * @param Zend_EventManager_EventCollection $events
     * @return Zend_EventManager_EventCollection
     */
    public function getEvents(Zend_EventManager_EventCollection $events = null)
    {
        if (null !== $events) {
            $this->_events = $events;
        } elseif (null === $this->_events) {
            $this->_events = new Zend_EventManager_EventManager(__CLASS__);
        }
        
        return $this->_events;
    }
    
    /**
     * sets all core indexes (clients, domains and nodes)
     */
    public function setCoreIndexes()
    {
        $this->setClients();
        $this->setDomains();
        $this->setNodes();
    }
    
    /**
     * builds and save core search index of a certain client
     * 
     * @return boolean
     */
    public function setClient($clientId)
    {
        if (!($client = Core_Model_DiFactory::getClientManager()->getClient($clientId))) {
            return false;
        }
        
        $data['id'] = $client->getId();
        $data['search'] = $client->getData('company');
        $data['search'] .= ' ' .$client->getData('surname');
        $data['search'] .= ' ' .$client->getData('prename');
        $data['headline'] = $client->getData('label');
        
        if($client->getData('company')) {
            $data['teaser'] = $client->getData('company') . ' - ';
            $data['teaser'] .= $client->getData('surname');
            $data['teaser'] .= ' ' .$client->getData('prename');
        } else {
            $data['teaser'] = $client->getData('surname');
            $data['teaser'] .= ' ' .$client->getData('prename');
        }
        
        $data['link'] = $this->_getUrlHelper()->url(array($client->getId(), $client->getLabel()), 'clientDetail');
        $data['categoryLink'] = $this->_getUrlHelper()->url(array(), 'clients');
        $data['pictureLeft'] = $this->_getUrlHelper()->url(array($client->getId()), 'avatar');
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_CLIENT, $clientId, $data);
    }
    
    /**
     * builds and save core search index of a certain domain
     * 
     * @return boolean
     */
    public function setDomain($domainId)
    {
        if (!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return false;
        }
        
        $data['id'] = $domain->getId();
        $data['search'] = $domain->getName();
        $data['headline'] = $domain->getName();
        $data['teaser'] = $domain->getName();
        $data['link'] = $this->_getUrlHelper()->url(array($domain->getName()), 'domaindetail');
        $data['categoryLink'] = $this->_getUrlHelper()->url(array(), 'domains');
        $data['pictureLeft'] = self::SEARCH_CATEGORY_DOMAIN_IMG;
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_DOMAIN, $domainId, $data);
    }
    
    /**
     * builds and save core search index of a certain node
     * 
     * @return boolean
     */
    public function setNode($nodeId)
    {
        if (!($node = Core_Model_DiFactory::getNodeManager()->getNode($nodeId))) {
            return false;
        }
        
        $data['id'] = $node->getId();
        $data['search'] = $node->getName() . ' ' . $node->getData('ipAddress');
        $data['headline'] = $node->getName();
        $data['teaser'] = $node->getName();
        $data['link'] = $this->_getUrlHelper()->url(array($node->getName()), 'nodedetail');
        $data['categoryLink'] = $this->_getUrlHelper()->url(array(), 'nodes');
        $data['pictureLeft'] = self::SEARCH_CATEGORY_NODE_IMG;
        
        return $this->_getSearchIndex()->setSearchIndex(self::SEARCH_CATEGORY_NODE, $nodeId, $data);
    }

    /**
     * builds and save core search index of all clients
     * 
     * @return boolean
     */
    public function setClients()
    {
        foreach(array_keys(Core_Model_DiFactory::getClientManager()->getClients()) as $clientId) {
            $this->setClient($clientId);
        }
    }
    
    /**
     * builds and save core search index of all domains
     * 
     * @return boolean
     */
    public function setDomains()
    {
        foreach(array_keys(Core_Model_DiFactory::getDomainManager()->getDomains()) as $domainId) {
            $this->setDomain($domainId);
        }
    }
    
    /**
     * builds and save core search index of all nodes
     * 
     * @return boolean
     */
    public function setNodes()
    {
        foreach(array_keys(Core_Model_DiFactory::getNodeManager()->getNodes()) as $nodeId) {
            $this->setNode($nodeId);
        }
    }
    
    /**
     * unsets a certain client in core search index
     * 
     * @param string $clientId
     * @return boolean
     */
    public function unsetClient($clientId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_CLIENT, $clientId);
    }
    
    /**
     * unsets a certain domain in core search index
     * 
     * @param string $domainId
     * @return boolean
     */
    public function unsetDomain($domainId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_DOMAIN, $domainId);
    }
    
    /**
     * unsets a certain node in core search index
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function unsetNode($nodeId)
    {
        return $this->_getSearchIndex()->deleteIndex(self::SEARCH_CATEGORY_NODE, $nodeId);
    }
    
}