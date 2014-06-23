<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Domain 
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase 
    implements Core_Model_Dataprovider_Interface_Domain
{

    CONST COLLECTION = 'domain';

    /**
     * deletes a certain domain
     * 
     * @param string $domainId
     * @return boolean
     */
    public function deleteDomain($domainId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if(!isset($collection[$domainId])) {
            return false;
        }
        
        unset($collection[$domainId]);
        $this->_setCollection(self::COLLECTION, $collection);
        
        return true;
    }
    
    /**
     * returns a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getDomain($domainId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($index == $domainId) {
                return $value;
            }
        }

        return false;
    }

    /**
     * returns a certain domain
     *
     * @param string $name
     * @return boolean 
     */
    public function getDomainByName($name)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($value['name'] == $name) {
                return $value;
            }
        }

        return false;
    }

    /**
     * returns all existing domains
     * 
     * @return array
     */
    public function getDomains()
    {
        return $this->_getCollection(self::COLLECTION);
    }

    /**
     * get domains by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getDomainsByLastModification($limit)
    {
        return array();
    }
    
    /**
     * returns all domains by the client id (owner)
     * 
     * @param string $clientId
     * @return array
     */
    public function getDomainsByOwner($clientId)
    {
        $domains = $this->_getCollection(self::COLLECTION);
        $return = array();

        foreach ($domains as $domainId => $domain) {
            if (isset($domain['owner']) && $domain['owner'] == $clientId) {
                $return[$domainId] = $domain;
            }
        }

        return $return;
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
        return array();
    }

    /**
     * paginates domains
     *
     * example return:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     *
     * @param int $limit
     * @param int $page
     * @param string $searchTerm
     * @return array
     */
    public function paginate($limit, $page, $searchTerm = null)
    {
        return array(
            'total' => 0,
            'data' => array()
        );
    }

    /**
     * updates/create domain with the given data set
     * 
     * @param array $data
     * @param string $domainId
     * @return boolean
     */
    public function saveDomain($data, $domainId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if(!$domainId) {
            $domainId = $this->_generateId();
        }
        
        if (!array_key_exists($domainId, $collection)) {
            $domainId = $data['_id'] = $domainId;
            $collection[$domainId] = array();
        }

        $collection[$domainId] = array_merge($collection[$domainId], $data);
        $this->_setCollection(self::COLLECTION, $collection);

        return $domainId;
    }
    
    public function updateDomain($id, $domainData)
    {
        $domain = $this->getDomainById($id);

        foreach ($domainData as $key => $value) {
            $domain[$key] = $value;
        }

        $collection = $this->_getCollection(self::COLLECTION);
        $collection[$id] = $domain;
        $this->_setCollection(self::COLLECTION, $collection);
    }

}
