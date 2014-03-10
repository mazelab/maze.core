<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Domain 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Domain
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'domain';
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name name
     */
    CONST KEY_NAME = 'name';

    /**
     * key name node
     */
    CONST KEY_NODE = 'nodes';
    
    /**
     * key name owner
     */
    CONST KEY_OWNER = 'owner';
    
    /**
     * key name for service property
     */
    CONST KEY_SERVICE = 'services';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getDomainCollection()->ensureIndex(array(
            self::KEY_OWNER => 1
        ));
        
        $this->_getDomainCollection()->ensureIndex(array(
            self::KEY_NAME => 1
        ), array('unique' => true));
    }
    
    /**
     * gets domain collection
     * 
     * @return MongoCollection
     */
    protected function _getDomainCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * deletes a certain domain
     * 
     * @param string $domainId
     * @return boolean
     */
    public function deleteDomain($domainId)
    {
        $query = array(
            self::KEY_ID => new MongoId($domainId)
        );
        
        $options = array(
            "j" => true
        );
        
        return $this->_getDomainCollection()->remove($query, $options);
    }
    
    /**
     * returns a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getDomain($domainId)
    {
        $query = array(
            self::KEY_ID => new MongoId($domainId)
        );
        
        if(!($domain = $this->_getDomainCollection()->findOne($query)) || empty($domain)) {
            return array();
        }
        
        $domain['_id'] = (string) $domain['_id'];
        return $domain;
    }
    
    /**
     * returns a certain domain by domain name
     * 
     * @param string $domainName
     * @return array
     */
    public function getDomainByName($domainName)
    {
        $query = array(
            self::KEY_NAME => new MongoRegex("/^$domainName$/i")
        );
        
        if(!($domain = $this->_getDomainCollection()->findOne($query)) || empty($domain)) {
            return array();
        }
        
        $domain['_id'] = (string) $domain['_id'];
        return $domain;
    }
    
    /**
     * returns all existing domains
     * 
     * @return array
     */
    public function getDomains()
    {
        $domains = array();
        $sort = array(
            self::KEY_ID => 1
        );
        
        foreach($this->_getDomainCollection()->find()->sort($sort) as $domainId => $domain) {
            $domain[self::KEY_ID] = $domainId;
            $domains[$domainId] = $domain;
        }
        
        return $domains;
    }

    /**
     * get domains by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getDomainsByLastModification($limit)
    {
        $domains = array();
        $sort = array(
            array('modified' => -1)
        );

        foreach ($this->_getDomainCollection()->find()->limit($limit)->sort($sort) as $domainId => $domain) {
            $domain[self::KEY_ID] = $domainId;
            $domains[$domainId] = $domain;
        }

        return $domains;
    }
    
    /**
     * returns all domains by the client id (owner)
     * 
     * @param string $clientId
     * @return array
     */    
    public function getDomainsByOwner($clientId)
    {
        $domains = array();
        $query = array(
            self::KEY_OWNER => (string) $clientId
        );
        
        $sort = array(
            self::KEY_ID => 1
        );
        
        foreach($this->_getDomainCollection()->find($query)->sort($sort) as $domainId => $domain) {
            $domain[self::KEY_ID] = $domainId;
            $domains[$domainId] = $domain;
        }

        return $domains;
    }
    
    /**
     * get domains which have the certain service
     * 
     * @param string $serviceName
     * @param string $clientId only domains of this client
     * @return array
     */
    public function getDomainsByService($serviceName, $clientId = null)
    {
        $domains = array();
        $query = array(
            self::KEY_SERVICE . '.' . (string) $serviceName => array(
                '$exists' => 1
            )
        );

        if($clientId) {
            $query[self::KEY_OWNER] = $clientId;
        }
        
        foreach($this->_getDomainCollection()->find($query) as $domainId => $domain) {
            $domain[self::KEY_ID] = $domainId;
            $domains[$domainId] = $domain;
        }

        return $domains;
    }

    /**
     * save domain data and return id
     * 
     * @param array $data
     * @param string $id
     * @return boolean
     */
    public function saveDomain($data, $id = null)
    {
        $mongoId = new MongoId($id);

        $data[self::KEY_ID] = $mongoId;
        
        $options = array(
            "j" => true
        );
        
        if(!$this->_getDomainCollection()->save($data, $options)) {
            return false;
        }
        
        return (string) $mongoId;
    }

}
