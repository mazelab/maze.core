<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Client
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Client
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
    implements Core_Model_Dataprovider_Interface_Client
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'user';

    /**
     * key name group
     */
    CONST KEY_GROUP = "group";
    
    /**
     * name of the clients group
     */
    CONST GROUP_CLIENT = "client";
    
    /**
     * delete client
     * 
     * @param string $clientId
     * @return boolean
     */
    public function deleteClient($clientId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if(!isset($collection[$clientId])) {
            return false;
        }
        
        unset($collection[$clientId]);
        $this->_setCollection(self::COLLECTION, $collection);
        
        return true;
    }
    
    /**
     * get client
     * 
     * @param string $clientId
     * @return array|null 
     */
    public function getClient($clientId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($index == $clientId) {
                return $value;
            }
        }

        return null;
    }
    
    /**
     * get client by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getClientByEmail($email)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($value['email'] == $email) {
                return $value;
            }
        }

        return null;
    }
    
    /**
     * get client by label
     * 
     * @param string $label
     * @return array|null
     */
    public function getClientByLabel($label)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($value['label'] == $label) {
                return $value;
            }
        }

        return null;
    }
    
    /**
     * get client by user name
     * 
     * @param string $userName
     * @return array 
     */
    public function getClientByUserName($userName)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($value['username'] == $userName) {
                return $value;
            }
        }

        return null;
    }
    
    /**
     * gets user data of clients
     * 
     * @return array
     */
    public function getClients()
    {
        $clients = array();

        foreach ($this->_getCollection(self::COLLECTION) as $client){
            if (!isset($client[self::KEY_GROUP]) || $client[self::KEY_GROUP] != self::GROUP_CLIENT){
                continue;
            }
            $clients[] = $client;
        }

        return $clients;
    }

    /**
     * get clients by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getClientsByLastModification($limit)
    {
        return $this->getClients();
    }
    
    /**
     * get clients which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getClientsByService($serviceName)
    {
        return array();
    }
    
    /**
     * save client
     * 
     * @param string $data
     * @param string $clientId
     * @return string id of saved client
     */
    public function saveClient($data, $clientId = null)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if(!$clientId) {
            $clientId = $this->_generateId();
        }
        
        if (!array_key_exists($clientId, $collection)) {
            $clientId = $data['_id'] = $clientId;
            $collection[$clientId] = array();
        }

        $collection[$clientId] = array_merge($collection[$clientId], $data);
        $this->_setCollection(self::COLLECTION, $collection);

        return $clientId;
    }
    
}
