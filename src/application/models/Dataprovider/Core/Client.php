<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Client
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Client
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Client
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'user';
    
    /**
     * key name email
     */
    CONST KEY_EMAIL = 'email';
    
    /**
     * key name label
     */
    CONST KEY_LABEL = 'label';
    
    /**
     * key name group
     */
    CONST KEY_GROUP = 'group';
    
    /**
     * value group type client
     */
    CONST KEY_GROUP_CLIENT = Core_Model_UserManager::GROUP_CLIENT;
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name user name
     */
    CONST KEY_USERNAME = 'username';
    
    /**
     * key name of serivce property
     */
    CONST KEY_SERVICE = 'services';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_EMAIL => 1,
            self::KEY_GROUP => 1
        ));
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_LABEL => 1,
            self::KEY_GROUP => 1
        ));
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_GROUP => 1
        ));
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_USERNAME => 1
        ), array('unique' => true));
    }
    
    /**
     * gets user collection
     * 
     * @return MongoCollection
     */
    protected function _getUserCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * delete client
     * 
     * @param string $clientId
     * @return boolean
     */
    public function deleteClient($clientId)
    {
        $query = array(
            self::KEY_ID => new MongoId($clientId),
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        $options = array(
            'j' => true
        );
        
        return $this->_getUserCollection()->remove($query, $options);
    }
    
    /**
     * get client
     * 
     * @param string $clientId
     * @return array 
     */
    public function getClient($clientId)
    {
        $query = array(
            self::KEY_ID => new MongoId($clientId),
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        if(!($client = $this->_getUserCollection()->findOne($query)) || empty($client)) {
            return array();
        }
        
        $client[self::KEY_ID] = (string) $client[self::KEY_ID];
        return $client;
    }
    
    /**
     * get client by email
     * 
     * @param string $email
     * @return array 
     */
    public function getClientByEmail($email)
    {
        $query = array(
            self::KEY_EMAIL => $email,
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        if(!($client = $this->_getUserCollection()->findOne($query)) || empty($client)) {
            return array();
        }
        
        $client[self::KEY_ID] = (string) $client[self::KEY_ID];
        return $client;
    }
    
    /**
     * get client by label
     * 
     * @param string $label
     * @return array 
     */
    public function getClientByLabel($label)
    {
        $query = array(
            self::KEY_LABEL => $label,
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        if(!($client = $this->_getUserCollection()->findOne($query)) || empty($client)) {
            return array();
        }
        
        $client[self::KEY_ID] = (string) $client[self::KEY_ID];
        return $client;
    }
    
    /**
     * get client by user name
     * 
     * @param string $userName
     * @return array 
     */
    public function getClientByUserName($userName)
    {
        $query = array(
            self::KEY_USERNAME => $userName,
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        if(!($client = $this->_getUserCollection()->findOne($query)) || empty($client)) {
            return array();
        }
        
        $client[self::KEY_ID] = (string) $client[self::KEY_ID];
        return $client;
    }
    
    /**
     * gets client data of clients
     * 
     * @return array
     */
    public function getClients()
    {
        $clients = array();
        $query = array(
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        foreach($this->_getUserCollection()->find($query) as $clientId => $client) {
            $client[self::KEY_ID] = $clientId;
            $clients[$clientId] = $client;
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
        $clients = array();
        $query  = array(
            self::KEY_GROUP => self::KEY_GROUP_CLIENT
        );
        
        $sort = array(
            'modified' => -1
        );

        foreach ($this->_getUserCollection()->find($query)->limit($limit)->sort($sort) as $clientId => $client) {
            $client[self::KEY_ID] = $clientId;
            $clients[$clientId] = $client;
        }

        return $clients;
    }
    
    /**
     * get clients which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getClientsByService($serviceName)
    {
        $clients = array();
        $query = array(
            self::KEY_SERVICE . '.' . (string) $serviceName => array(
                '$exists' => 1
            )
        );

        foreach($this->_getUserCollection()->find($query) as $clientId => $client) {
            $client[self::KEY_ID] = $clientId;
            $clients[$clientId] = $client;
        }

        return $clients;
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
        $mongoId = new MongoId($clientId);

        $data[self::KEY_ID] = $mongoId;
        
        $options = array(
            "j" => true
        );
        
        if(!$this->_getUserCollection()->save($data, $options)) {
            return false;
        }
        
        return (string) $mongoId;
    }
    
}
