<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ClientManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ClientManager
{

    /**
     * name of the clients group
     */
    CONST GROUP_CLIENT = Core_Model_UserManager::GROUP_CLIENT;
    
    /**
     * message when client was activated
     */
    CONST MESSAGE_CLIENT_ACTIVATED = 'Client %1$s was activated';
    
    /**
     * message when client was created
     */
    CONST MESSAGE_CLIENT_CREATED = 'Client %1$s was created';
    
    /**
     * message when client was deactivated
     */
    CONST MESSAGE_CLIENT_DEACTIVATED = 'Client %1$s was deactivated';
    
    /**
     * message when client was deleted
     */
    CONST MESSAGE_CLIENT_DELETED = 'Client %1$s was deleted';

    /**
     * message when service was added to a client
     */
    CONST MESSAGE_CLIENT_SERVICE_ADD = 'Service %1$s was added to client %2$s';

    /**
     * message when service remove failed
     */
    CONST MESSAGE_CLIENT_SERVICE_REMOVE_FAILED = 'Failed to remove Service %1$s';
    
    /**
     * message when client was updated
     */
    CONST MESSAGE_CLIENT_UPDATED = 'Client %1$s was updated';
    
    /**
     * message when client password was updated
     */
    CONST MESSAGE_CLIENT_UPDATED_PASSWORD = 'Password of client %1$s was changed';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain client instance if registered
     * 
     * @param string $clientId
     * @return Vpopmail_Model_ValueObject_Client|null null if not registered
     */
    protected function _getRegisteredClient($clientId)
    {
        if(!$this->isClientRegistered($clientId)) {
            return null;
        }
        
        return Core_Model_DiFactory::getClient($clientId);
    }
    
    /**
     * loads and registers a certain client instance
     * 
     * @param string $clientId
     * @return boolean
     */
    protected function _loadClient($clientId)
    {
        if(!$clientId) {
            return null;
        }
        
        $data = $this->getProvider()->getClient($clientId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerClient($clientId, $data);
    }
    
    /**
     * loads and registers a certain client instance by email
     * 
     * @param string $email
     * @return boolean
     */
    protected function _loadClientByEmail($email)
    {
        if(!$email) {
            return null;
        }
        
        $data = $this->getProvider()->getClientByEmail($email);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerClient($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * loads and registers a certain client instance by label
     * 
     * @param string $label
     * @return boolean
     */
    protected function _loadClietnByLabel($label)
    {
        if(!$label) {
            return null;
        }
        
        $data = $this->getProvider()->getClientByLabel($label);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerClient($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * loads and registers a certain client instance
     * 
     * @param string $userName
     * @return boolean
     */
    protected function _loadClientByUserName($userName)
    {
        if(!$userName) {
            return null;
        }
        
        $data = $this->getProvider()->getClientByUserName($userName);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerClient($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }

    /**
     * updates client services via boolean values
     *
     * services schema:
     * array(
     *     'service1' => true,          # enables service1
     *     'service2' => false          # disables service2
     * )
     *
     * @param string $clientId
     * @param array $services
     * @return boolean
     */
    public function _updateClientServices($clientId, array $services)
    {
        foreach($services as $service => $state) {
            if(!filter_var($state, FILTER_VALIDATE_BOOLEAN)) {
                if(!$this->removeClientService($clientId, $service)) {
                    return false;
                }
            } else {
                if(!$this->addService($clientId, $service)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * adds an additional field to the given client
     * 
     * @param string $clientId
     * @param array $data
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($clientId, $data)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }

        if (!array_key_exists("additionalKey", $data) || !array_key_exists("additionalValue", $data)){
            return false;
        }

        if (!($additionalId = $client->addAdditionalField($data['additionalKey'], $data['additionalValue'])) ||
                !$client->save()) {
            return false;
        }

        return $additionalId;
    }
    
    /**
     * adds a certain service to a certain client
     * 
     * @param string $clientId
     * @param string $serviceName name of the service/module
     * @return boolean
     */
    public function addService($clientId, $serviceName)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }
        if($client->hasService($serviceName)) {
            return true;
        }
        
        if(!$client->addService($serviceName)) {
            return false;
        }

        $module = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_CLIENT_SERVICE_ADD)
                ->setMessageVars($module->getLabel(), $client->getLabel())
                ->setClientRef($clientId)->save();
        
        return true;
    }
    
    /**
     * changes the status of the given client
     * 
     * @param string $clientId
     * @return boolean
     */
    public function changeClientState($clientId)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }
        
        $currentStatus = $client->getStatus();
        if(!$currentStatus || $currentStatus === false) {
            if(!$client->activate()) {
                return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_CLIENT_ACTIVATED);
        } else {
            if(!$client->deactivate()) {
                return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_CLIENT_DEACTIVATED);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setClientRef($client->getId())->setMessageVars($client->getLabel())
                ->save();
        
        return true;
    }
    
    /**
     * adds a new client with the certain data
     * 
     * @param array $data
     * @return Core_Model_ValueObject_Client
     */
    public function createClient($data)
    {
        if(!isset($data['username'])) {
            return false;
        }

        $client = Core_Model_DiFactory::newClient();
        $data['group'] = self::GROUP_CLIENT;
        $data["status"] = isset($data["status"]) ? (boolean) $data["status"] : true;
        
        if (!$client->setData($data)->save()) {
            return false;
        }
        
        $this->registerClient($client->getId(), $client);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
            ->setMessage(self::MESSAGE_CLIENT_CREATED)->setMessageVars($client->getLabel())
            ->setClientRef($client->getId())->setData($client->getData())->save();
        
        return $client;
    }

    /**
     * deletes a certain additional field from this client in the data backend
     *
     * @param string $clientId
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($clientId, $key)
    {
        if(!($client = $this->getClient($clientId))) {
            return true;
        }

        if (!$client->deleteAdditionalField($key) || !$client->save())  {
            return false;
        }
        
        return true;
    }
    
    /**
     * deletes a certain client
     * 
     * @param string $clientId
     * @return boolean 
     */
    public function deleteClient($clientId)
    {
        if (!($client = $this->getClient($clientId))) {
            return false;
        }

        if(!$this->removeClientDomains($clientId)) {
            return false;
        }
        if(!$this->removeClientServices($clientId)) {
            return false;
        }
        $client->removeAvatar();
        if(!$this->getProvider()->deleteClient($clientId)) {
            return false;
        }

        Core_Model_DiFactory::getIndexManager()->unsetClient($clientId);
        $this->unregisterClient($clientId);
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_CLIENT_DELETED)->setMessageVars($client->getLabel())
                ->save();
        
        return true;
    }

    /**
     * returns a certain client object
     * 
     * @param string $clientId
     * @return Core_Model_ValueObject_Client
     */
    public function getClient($clientId)
    {
        if(!$this->isClientRegistered($clientId)) {
            $this->_loadClient($clientId);
        }
        
        return $this->_getRegisteredClient($clientId);
    }
    
    /**
     * gets data set of a certain client
     * 
     * @param string $clientId
     * @return array
     */
    public function getClientAsArray($clientId)
    {
        if(!$this->isClientRegistered($clientId)) {
            $this->_loadClient($clientId);
        }
        
        if(!($client = $this->_getRegisteredClient($clientId))) {
            return array();
        }
        
        return $client->getData();
    }

    /**
     * gets complete client data enriched with api dependencies for api use
     *
     * @param string $clientId
     * @return array|null
     */
    public function getClientForApi($clientId)
    {
        if(!($client = $this->getClient($clientId))) {
            return null;
        }

        return $client->getDataForApi();
    }
    
    /**
     * gets client object by domain
     * 
     * @param string $domainId
     * @return Core_Model_ValueObject_Client|null
     */
    public function getClientByDomain($domainId)
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return null;
        }
        
        return $this->getClient($domain->getData('owner'));
    }
    
    /**
     * gets client data by domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getClientByDomainAsArray($domainId)
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomain($domainId))) {
            return array();
        }
        
        if(!($client = $this->getClient($domain->getData('owner')))) {
            return array();
        }
        
        return $client->getData();
    }
    
    /**
     * return client instance by email
     * 
     * @param string $email
     * @return Core_Model_ValueObject_Client|null
     */
    public function getClientByEmail($email)
    {
        if(($client = Core_Model_DiFactory::getClientByEmail($email))) {
            return $client;
        }
        
        if(!($clientId = $this->_loadClientByEmail($email))) {
            return null;
        }
        
        return $this->_getRegisteredClient($clientId);
    }
    
    /**
     * return client instance by email as array
     * 
     * @param string $email
     * @return array
     */
    public function getClientByEmailAsArray($email)
    {
        if(!($client = $this->getClientByEmail($email))) {
            return array();
        }
        
        return $client->getData();
    }
    
    /**
     * return client instance by label
     * 
     * @param string $label
     * @return Core_Model_ValueObject_Client|null
     */
    public function getClientByLabel($label)
    {
        if(($client = Core_Model_DiFactory::getClientByLabel($label))) {
            return $client;
        }
        
        if(!($clientId = $this->_loadClietnByLabel($label))) {
            return null;
        }
        
        return $this->_getRegisteredClient($clientId);
    }
    
    /**
     * return client instance by label as array
     * 
     * @param string $label
     * @return array
     */
    public function getClientByLabelAsArray($label)
    {
        if(!($client = $this->getClientByLabel($label))) {
            return array();
        }
        
        return $client->getData();
    }
    
    /**
     * return client instance by user name
     * 
     * @param string $userName
     * @return Core_Model_ValueObject_Client|null
     */
    public function getClientByUserName($userName)
    {
        if(($client = Core_Model_DiFactory::getClientByUserName($userName))) {
            return $client;
        }
        
        if(!($clientId = $this->_loadClientByUserName($userName))) {
            return null;
        }
        
        return $this->_getRegisteredClient($clientId);
    }
    
    /**
     * return client instance by user name as array
     * 
     * @param string $userName
     * @return array
     */
    public function getClientByUserNameAsArray($userName)
    {
        if(!($client = $this->getClientByUserName($userName))) {
            return array();
        }
        
        return $client->getData();
    }
    
    /**
     * get services of a client
     * 
     * @param string $clientId
     * @return array
     */
    public function getClientServices($clientId)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }
        
        return $client->getServices();
    }
    
    /**
     * gets all existing clients
     * 
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClients()
    {
        $clients = array();
        
        foreach($this->getProvider()->getClients() as $clientId => $client) {
            $this->registerClient($clientId, $client);
            $clients[$clientId] = $this->_getRegisteredClient($clientId);
        }
        
        return $clients;
    }
    
    /**
     * gets all existing clients as array
     * 
     * @return array
     */
    public function getClientsAsArray()
    {
        $clients = array();
        
        foreach($this->getProvider()->getClients() as $clientId => $client) {
            $this->registerClient($clientId, $client);
            $clients[$clientId] = $client;
        }
        
        return $clients;
    }
    
    /**
     * returns the last modificated clients
     * 
     * @param integer $limit limit the search result
     * @return array contains Core_Model_ValueObject_Client
     */
    public function getClientsByLastModification($limit = 5)
    {
        $clients = array();
        
        foreach($this->getProvider()->getClientsByLastModification($limit) as $clientId => $client) {
            $this->registerClient($clientId, $client);
            $clients[$clientId] = $this->_getRegisteredClient($clientId);
        }
        
        return $clients;
    }
    
    /**
     * returns the last modificated clients
     * 
     * @param integer $limit limit the search result
     * @return array
     */
    public function getClientsByLastModificationAsArray($limit = 5)
    {
        $clients = array();
        
        foreach($this->getProvider()->getClientsByLastModification($limit) as $clientId => $client) {
            $this->registerClient($clientId, $client);
            $clients[$clientId] = $client;
        }
        
        return $clients;
    }
    
    /**
     * get clients which have the certain service
     * 
     * @param string $serviceName
     * @return array contains instances of Core_Model_ValueObject_Client
     */
    public function getClientsByService($serviceName)
    {
        $clients = array();
        
        foreach($this->getProvider()->getClientsByService($serviceName) as $clientId => $client) {
            if(!$this->_getRegisteredClient($clientId)) {
                $this->registerClient($clientId, $client);
            }
            
            $clients[$clientId] = $this->getClient($clientId);
        }
        
        return $clients;
    }
    
    /**
     * get clients which have the certain service as array
     * 
     * @param string $serviceName
     * @return array contains instances of Core_Model_ValueObject_Client
     */
    public function getClientsByServiceAsArray($serviceName)
    {
        $clients = array();
        
        foreach($this->getClientsByService($serviceName) as $clientId => $client) {
            $clients[$clientId] = $client->getData();
        }
        
        return $clients;
    }
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Client
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getClient();
    }
    
    /**
     * checks if a certain client instance is allready registered
     * 
     * @param string $clientId
     * @return boolean
     */
    public function isClientRegistered($clientId)
    {
        if(Core_Model_DiFactory::isClientRegistered($clientId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a client instance
     * 
     * overwrites existing instances
     * 
     * @param string $clientId
     * @param mixed $context array or Vpopmail_Model_ValueObject_Client
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerClient($clientId, $context, $setLoadedFlag = true)
    {
        $client = null;
        
        if(is_array($context)) {
            $client = Core_Model_DiFactory::newClient($clientId);
            
            if($setLoadedFlag) {
                $client->setLoaded(true);
            }
            
            $client->getBean()->setBean($context, true, true);
        } elseif($context instanceof Core_Model_ValueObject_Client) {
            $client = $context;
        }
        
        if(!$client || !$client instanceof Core_Model_ValueObject_Interface_User) {
            return false;
        }
        
        Core_Model_DiFactory::registerClient($clientId, $client);
        
        return true;
    }

    /**
     * removes all domains of a certain client
     *
     * @param string $clientId
     * @return bool
     */
    public function removeClientDomains($clientId)
    {
        if (!($client = $this->getClient($clientId))) {
            return false;
        }

        $domainManager = Core_Model_DiFactory::getDomainManager();
        foreach ($domainManager->getDomainsByOwner($client->getId()) as $domain) {
            if (!$domainManager->deleteDomain($domain->getId())) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a certain client service
     *
     * @param string $clientId id of the client
     * @param string $service name of the service
     * @return boolean
     */
    public function removeClientService($clientId, $service)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }
        if(!$client->hasService($service)) {
            return true;
        }

        if (!Core_Model_DiFactory::getModuleApi()->removeClient($clientId, $service)) {
            Core_Model_DiFactory::getMessageManager()->addError(self::MESSAGE_CLIENT_SERVICE_REMOVE_FAILED, $service);
            return false;
        }

        if (count($client->getData("services")) == 1){
            $client->unsetProperty("services");
        }else {
            $client->unsetProperty("services/$service");
        }

        return $client->save();
    }

    /**
     * remove a all client services
     *
     * @param string $clientId id of the client
     * @return boolean
     */
    public function removeClientServices($clientId)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }
        if(!($services = $client->getServices())) {
            return true;
        }

        foreach(array_keys($services) as $serviceName) {
            if(!$this->removeClientService($clientId, $serviceName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a certain service on all clients
     *
     * @param string $service name of the service
     * @return boolean
     */
    public function removeClientsService($service)
    {
        foreach(array_keys($this->getClientsByService($service)) as $clientId) {
            if(!$this->removeClientService($clientId, $service)) {
                return false;
            }
        }

        return true;
    }

    /**
     * unregister a certain client instance
     * 
     * @param string $clientId
     * @return boolean
     */
    public function unregisterClient($clientId)
    {
        if(!$this->_getRegisteredClient($clientId)) {
            return true;
        }
        
        Core_Model_DiFactory::unregisterClient($clientId);
    }
    
    /**
     * updates a certain client
     * 
     * @param string $clientId
     * @param array $data
     * @return boolean
     */
    public function updateClient($clientId, array $data)
    {
        if(!($client = $this->getClient($clientId))) {
            return false;
        }

        if(array_key_exists('services', $data) && is_array($data['services'])) {
            if(!$this->_updateClientServices($clientId, $data['services'])) {
                return false;
            }
            unset($data['services']);
        }

        if (isset($data['additionalKey']) && isset($data['additionalValue'])) {
            $client->addAdditionalField($data['additionalKey'], $data['additionalValue']);
            unset($data['additionalKey'], $data['additionalValue']);
        }

        if(isset($data['additionalFields'])) {
            foreach ($data['additionalFields'] as $id => $additionalField) {
                if (!$additionalField["value"] || trim($additionalField["value"]) == "") {
                    $this->deleteAdditionalField($clientId, $id);
                    unset($data['additionalFields'][$id]);
                }
            }

            if(empty($data['additionalFields'])) {
                unset($data['additionalFields']);
            }
            
            if(empty($data)) {
                return true;
            }
        }

        if(!$client->setData($data)->save()) {
            return false;
        }
        
        if(isset($data['password'])) {
            $this->_getLogger()->setMessage(self::MESSAGE_CLIENT_UPDATED_PASSWORD);
        } else {
            $this->_getLogger()->setMessage(self::MESSAGE_CLIENT_UPDATED)->setData($data);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessageVars($client->getLabel())
                ->setClientRef($clientId)->save();
        
        return true;
    }
    
}

