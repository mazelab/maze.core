<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_DomainManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_DomainManager
{
    
    /**
     * message when domain allready exists
     */
    CONST DOMAIN_EXISTS = 'domain %1$s allready exists!';

    /**
     * log action for unregistered domains
     */
    CONST LOG_ACTION_UNREGISTERED_DOMAIN = 'unknown domain';
    
    /**
     * message when domain was activated
     */
    CONST MESSAGE_DOMAIN_ACTIVATED = 'Domain %1$s was activated';
    
    /**
     * message when domain was created
     */
    CONST MESSAGE_DOMAIN_CREATED = 'Domain %1$s was created';
    
    /**
     * message when domain was deactivated
     */
    CONST MESSAGE_DOMAIN_DEACTIVATED = 'Domain %1$s was deactivated';

    /**
     * message when domain was deleleted
     */
    CONST MESSAGE_DOMAIN_DELETED = 'Domain %1$s was deleted';
    
    /**
     * message when domain was registered
     */
    CONST MESSAGE_DOMAIN_REGISTERED = 'Domain %1$s was registered';
    
    /**
     * message when service was added to a domain
     */
    CONST MESSAGE_DOMAIN_SERVICE_ADD = 'Service %1$s was added to domain %2$s';

    /**
     * message when domain was updated
     */
    CONST MESSAGE_DOMAIN_UPDATED = 'Domain %1$s was updated';
    
    /**
     * message when unknown domain was reported
     */
    CONST MESSAGE_UNREGISTERED_DOMAIN = 'A report has reported the unregistered domain %1$s';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain domain instance if registered
     * 
     * @param string $domainId
     * @return Vpopmail_Model_ValueObject_Domain|null null if not registered
     */
    protected function _getRegisteredDomain($domainId)
    {
        if(!$this->isDomainRegistered($domainId)) {
            return null;
        }
        
        return Core_Model_DiFactory::getDomain($domainId);
    }
    
    /**
     * loads and registers a certain domain instance
     * 
     * @param string $domainId
     * @return boolean
     */
    protected function _loadDomain($domainId)
    {
        if(!$domainId) {
            return null;
        }
        
        $data = $this->getProvider()->getDomain($domainId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerDomain($domainId, $data);
    }
    
    /**
     * loads and registers a certain domain instance by domain name
     * 
     * @param string $domainName
     * @return string domain id
     */
    protected function _loadDomainByName($domainName)
    {
        if(!$domainName) {
            return null;
        }
        
        $data = $this->getProvider()->getDomainByName($domainName);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return false;
        }
        
        $this->registerDomain($data['_id'], $data);
        
        return $data['_id'];
    }

    /**
     * updates additional fields
     *
     * services schema:
     * array(
     *     'e3d704f3542b44a621ebed70dc0efe13' => array( # update/set additional field
     *          'label' => 'test1',
     *          'value' => 'test'
     * ),
     *     'e3d704f3542b44a621ebed70dc0efe15' => array( # remove additional field
     *          'value' =>
     * )
     *
     * @param string $domainId
     * @param array $data
     * @return boolean
     */
    public function _updateAdditionalFields($domainId, array $data)
    {
        if(!$data) {
            return true;
        }
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        $updateData = $data;
        foreach($data as $key => $additionalField) {
            if(!array_key_exists('value', $additionalField)) {
                unset($updateData[$key]);
                continue;
            }

            if($additionalField['value']) {
                if(!$domain->getData("additionalFields/$key")) {
                    return false;
                } elseif (array_key_exists('label', $additionalField)) {
                    unset($updateData[$key]['label']);
                }
            } else {
                if(!$domain->deleteAdditionalField($key)) {
                    return false;
                }
                unset($updateData[$key]);
            }

        }

        if($updateData) {
            $domain->setData(array('additionalFields' => $updateData));
        }

        return true;
    }

    /**
     * updates domain services via boolean values
     *
     * services schema:
     * array(
     *     'service1' => true,          # enables service1
     *     'service2' => false          # disables service2
     * )
     *
     * @param string $domainId
     * @param array $services
     * @return boolean
     */
    public function _updateDomainServices($domainId, array $services)
    {
        foreach($services as $service => $state) {
            if(!filter_var($state, FILTER_VALIDATE_BOOLEAN)) {
                if(!$this->removeDomainService($domainId, $service)) {
                    return false;
                }
            } else {
                if(!$this->addService($domainId, $service)) {
                    return false;
                }
            }
        }

        return true;
    }
    
    /**
     * adds an additional field to the given domain
     * 
     * @param string $domainId
     * @param array $data
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($domainId, $data)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        if (!array_key_exists("additionalKey", $data) || !array_key_exists("additionalValue", $data)
                || !($additionalId = $domain->addAdditionalField($data['additionalKey'], $data['additionalValue']))){
            return false;
        }
        
        if (!$domain->save()) {
            return false;
        }
        
        return $additionalId;
    }
    
    /**
     * adds a certain service to a certain domain
     * 
     * @param string $domainId
     * @param string $serviceName
     * @return boolean
     */
    public function addService($domainId, $serviceName)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }
        if($domain->hasService($serviceName)) {
            return true;
        }
        
        $apiBroker = new Core_Model_Module_Api();
        if (!$apiBroker->validateDomainForService($serviceName, $domainId)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError('The service \'%1$s\' could not be assigned to the domain \'%2$s\'', $serviceName, $domain->getName());
            return false;
        }
        
        if(!$domain->addService($serviceName)) {
            return false;
        }

        $module = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_DOMAIN_SERVICE_ADD)
                ->setMessageVars($module->getLabel(), $domain->getName())
                ->setDomainRef($domainId)->save();
        
        return true;
    }
    
    /**
     * creates a new domain with the given data
     * 
     * @param string $name
     * @param string $ownerId
     * @param string $procurement
     * @return string domain id
     */
    public function createDomain($name, $ownerId, $procurement = null)
    {
        if(($this->getDomainByName($name))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DOMAIN_EXISTS, $name);
            return false;
        }
        
        if(!($domain = Core_Model_DiFactory::newDomain())) {
            return false;
        }
        
        $data = array(
            'name' => $name,
            'owner' => $ownerId,
            'procurement' => $procurement,
            'status' => true
        );

        if(!Core_Model_DiFactory::getModuleApi()->preAddDomain($data) || !$domain->setData($data)->save()) {
            return false;
        }

        $this->registerDomain($domain->getId(), $domain);

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_DOMAIN_CREATED)
                ->setMessageVars($domain->getName())
                ->setClientRef($domain->getOwner()->getId())
                ->setDomainRef($domain->getId())
                ->save();

        Core_Model_DiFactory::getModuleApi()->postAddDomain($domain->getId());
        
        return $domain->getId();
    }
    
    /**
     * deletes a certain additional field from this domain in the data backend
     *
     * @param string $domainId
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($domainId, $key)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        if(!$domain->getData('additionalFields/' . $key)) {
            return true;
        }

        if (!$domain->deleteAdditionalField($key) || !$domain->save())  {
            return false;
        }

        return true;
    }
    
    /**
     * deletes a certain domain
     * 
     * @param string $domainId
     * @return boolean
     */
    public function deleteDomain($domainId)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        if(!$this->removeDomainServices($domainId)) {
            return false;
        }
        if(!$this->getProvider()->deleteDomain($domainId)) {
            return false;
        }

        Core_Model_DiFactory::getIndexManager()->unsetDomain($domainId);
        $this->unregisterDomain($domainId);
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_DOMAIN_DELETED)
                ->setMessageVars($domain->getName())
                ->setClientRef($domain->getOwner()->getId())->save();
        
        return true;
    }
    
    /**
     * returns a certain domain
     * 
     * @param string $domainId
     * @return Core_Model_ValueObject_Domain|null
     */
    public function getDomain($domainId)
    {
        if(!$this->isDomainRegistered($domainId)) {
            $this->_loadDomain($domainId);
        }
        
        return $this->_getRegisteredDomain($domainId);
    }
    
    /**
     * returns data set of a certain domain
     * 
     * @param domainId $domainId
     * @return array
     */
    public function getDomainAsArray($domainId)
    {
        if(!$this->isDomainRegistered($domainId)) {
            $this->_loadDomain($domainId);
        }
        
        if(!($domain = $this->_getRegisteredDomain($domainId))) {
            return array();
        }
        
        return $domain->getData();
    }
    
    /**
     * returns a certain domain by domain name
     * 
     * @param string $domainName
     * @return Core_Model_ValueObject_Domain|null
     */
    public function getDomainByName($domainName)
    {
        if(($domain = Core_Model_DiFactory::getDomainByName($domainName))) {
            return $domain;
        }
        
        if(!($domainId = $this->_loadDomainByName($domainName))) {
            return null;
        }
        
        return $this->_getRegisteredDomain($domainId);
    }
    
    /**
     * returns a certain domain by domain name
     * 
     * @param string $domainName
     * @return null|Core_Model_ValueObject_Domain
     */
    public function getDomainByNameAsArray($domainName)
    {
        if(($domain = Core_Model_DiFactory::getDomainByName($domainName))) {
            return $domain->getData();
        }
        
        if(!($domainId = $this->_loadDomainByName($domainName))) {
            return null;
        }
        
        if(!($domain = $this->_getRegisteredDomain($domainId))) {
            return array();
        }
        
        return $domain->getData();
    }

    /**
     * gets complete domain data enriched with api dependencies for api use
     *
     * @param string $domainId
     * @return array|null
     */
    public function getDomainForApi($domainId)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return null;
        }

        return $domain->getDataForApi();
    }
    
    /**
     * returns all existing domains
     * 
     * @return array
     */
    public function getDomains()
    {
        $return = array();

        foreach($this->getProvider()->getDomains() as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            
            if(($domain = $this->_getRegisteredDomain($domainId))) {
                $return[$domainId] = $domain;
            }
        }
        
        return $return;
    }

    /**
     * returns data set of all existing domains as array
     * 
     * @return array
     */
    public function getDomainsAsArray()
    {
        $return = array();

        foreach($this->getProvider()->getDomains() as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            $return[$domainId] = $domain;
        }
        
        return $return;
    }

    /**
     * get all domains of a certain client enriched with api dependencies for api use
     *
     * @param string $clientId
     * @return array
     */
    public function getDomainsByClientForApi($clientId)
    {
        $result = array();

        if(!$clientId || !($domains = Core_Model_DiFactory::getModuleListings()->getDomainsWithNodesByClient($clientId))) {
            return array();
        }

        foreach($domains as $domain) {
            array_push($result, $domain);
        }

        return $result;
    }
    
    /**
     * returns the last modificated domains
     * 
     * @param  integer $limit limit the search result
     * @return array
     */
    public function getDomainsByModificationDate($limit = 5)
    {
        $domains = array();
        
        foreach($this->getProvider()->getDomainsByLastModification($limit) as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            
            if(($domain = $this->_getRegisteredDomain($domainId))) {
                $domains[$domainId] = $domain;
            }
        }
        
        return $domains;
    }
    
    /**
     * returns the last modificated domains as array
     * 
     * @param  integer $limit limit the search result
     * @return array
     */
    public function getDomainsByModificationDateAsArray($limit = 5)
    {
        $domains = array();
        
        foreach($this->getProvider()->getDomainsByLastModification($limit) as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            $domains[$domainId] = $domain;
        }
        
        return $domains;
    }

    /**
     * get all domains of a certain node enriched with api dependencies for api use
     *
     * @param string $nodeId
     * @return array
     */
    public function getDomainsByNodeForApi($nodeId)
    {
        $result = array();

        if(!$nodeId || !($domains = Core_Model_DiFactory::getModuleListings()->getDomainsWithClientsByNode($nodeId))) {
            return array();
        }

        foreach($domains as $domain) {
            array_push($result, $domain);
        }

        return $result;
    }
    
    /**
     * returns all domains of a certain owner
     * 
     * @param string $clientId
     * @return array contains Core_Model_ValueObject_Domain
     */
    public function getDomainsByOwner($clientId)
    {
        $domains = array();
        
        foreach($this->getProvider()->getDomainsByOwner($clientId) as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            
            if(($domain = $this->_getRegisteredDomain($domainId))) {
                $domains[$domainId] = $domain;
            }
        }
        
        return $domains;
    }
    
    /**
     * returns all domains by a certain client (owner)
     * 
     * @param string $clientId
     * @return array
     */
    public function getDomainsByOwnerAsArray($clientId)
    {
        $domains = array();
        
        foreach($this->getProvider()->getDomainsByOwner($clientId) as $domainId => $domain) {
            $this->registerDomain($domainId, $domain);
            $domains[$domainId] = $domain;
        }
        
        return $domains;
    }
    
    /**
     * get domains which have the certain service
     * 
     * @param string $serviceName
     * @param string $clientId only domains of this client
     * @return array contains instances of Core_Model_ValueObject_Domain
     */
    public function getDomainsByService($serviceName, $clientId = null)
    {
        $domains = array();
        
        foreach($this->getProvider()->getDomainsByService($serviceName, $clientId) as $domainId => $domain) {
            if(!$this->_getRegisteredDomain($domainId)) {
                $this->registerDomain($domainId, $domain);
            }
            
            $domains[$domainId] = $this->getDomain($domainId);
        }
        
        return $domains;
    }
    
    /**
     * get domains which have the certain service as array
     * 
     * @param string $serviceName
     * @param string $clientId only domains of this client
     * @return array contains instances of Core_Model_ValueObject_Domain
     */
    public function getDomainsByServiceAsArray($serviceName, $clientId = null)
    {
        $domains = array();
        
        foreach($this->getDomainsByService($serviceName, $clientId) as $domainId => $domain) {
            $domains[$domainId] = $domain->getData();
        }
        
        return $domains;
    }
    
    /**
     * @return Core_Model_Dataprovider_Interface_Domain
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getDomain();
    }
    
    /**
     * checks if a certain domain instance is allready registered
     * 
     * @param string $domainId
     * @return boolean
     */
    public function isDomainRegistered($domainId)
    {
        if(Core_Model_DiFactory::isDomainRegistered($domainId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * logs unregistred domain which came through api
     * 
     * @param string $domainName
     * @param array $data
     * @param string $nodeId node context
     * @param string $moduleName module context
     * @return boolean
     */
    public function logUnregisteredDomain($domainName, array $data = null, $nodeId = null, $moduleName = null)
    {
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setAction(self::LOG_ACTION_UNREGISTERED_DOMAIN)
                ->setMessage(self::MESSAGE_UNREGISTERED_DOMAIN)
                ->setMessageVars($domainName)->setModuleRef($moduleName)
                ->setNodeRef($nodeId)->setData($data);
        
        return $this->_getLogger()->saveByContext($domainName);
    }

    /**
     * paginate domains
     *
     * example return:
     * array(
     *      'total' => 20,
     *      'data' => array(0 => array(...), ...)
     * )
     *
     * @param int $numPerPage
     * @param int $page
     * @param string $term
     * @return array
     */
    public function paginate($numPerPage = 10, $page = 1, $term = null)
    {
        if(!is_numeric($page)) {
            return array();
        }

        return $this->getProvider()->paginate($numPerPage, $page, $term);
    }

    /**
     * registers a domain instance
     * 
     * overwrites existing instances
     * 
     * @param string $domainId
     * @param mixed $context array or Vpopmail_Model_ValueObject_Domain
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerDomain($domainId, $context, $setLoadedFlag = true)
    {
        $domain = null;
        
        if(is_array($context)) {
            $domain = Core_Model_DiFactory::newDomain($domainId);
            
            if($setLoadedFlag) {
                $domain->setLoaded(true);
            }
            
            $domain->getBean()->setBean($context);
        } elseif($context instanceof Core_Model_ValueObject_Domain) {
            $domain = $context;
        }
        
        if(!$domain) {
            return false;
        }
        
        Core_Model_DiFactory::registerDomain($domainId, $domain);
        
        return true;
    }

    /**
     * remove a certain service on all domains
     *
     * @param string $service name of the service
     * @return boolean
     */
    public function removeDomainsService($service)
    {
        foreach(array_keys($this->getDomainsByService($service)) as $domainId) {
            if(!$this->removeDomainService($domainId, $service)) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a all domain services
     *
     * @param string $domainId id of the domain
     * @return boolean
     */
    public function removeDomainServices($domainId)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }
        if(!($services = $domain->getServices())) {
            return true;
        }

        foreach(array_keys($services) as $serviceName) {
            if(!$this->removeDomainService($domainId, $serviceName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a certain domain service
     *
     * @param string $domainId id of the client
     * @param string $service name of the service
     * @return boolean
     */
    public function removeDomainService($domainId, $service)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        return $domain->removeService($service);
    }

    /**
     * unregisters a certain domain instance
     * 
     * @param string $domainId
     * @return boolean
     */
    public function unregisterDomain($domainId)
    {
        if(!$this->_getRegisteredDomain($domainId)) {
            return true;
        }
        
        Core_Model_DiFactory::unregisterDomain($domainId);
    }
    
    /**
     * updates a certain domain with the given data
     * 
     * @param  string $domainId
     * @param  array $data
     * @return boolean
     */
    public function updateDomain($domainId, $data)
    {
        if(!($domain = $this->getDomain($domainId))) {
            return false;
        }

        if(array_key_exists('services', $data) && is_array($data['services'])) {
            if(!$this->_updateDomainServices($domainId, $data['services'])) {
                return false;
            }
            unset($data['services']);
        }

        if(array_key_exists('additionalFields', $data) && is_array($data['additionalFields'])) {
            if(!$this->_updateAdditionalFields($domainId, $data['additionalFields'])) {
                return false;
            }
            unset($data['additionalFields']);
        }

        if (isset($data['additionalKey']) && isset($data['additionalValue'])) {
            $domain->addAdditionalField($data['additionalKey'], $data['additionalValue']);
            unset($data['additionalKey'], $data['additionalValue']);
        }

        if(!$domain->setData($data)->save()) {
            return false;
        }

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_DOMAIN_UPDATED)
                ->setMessageVars($domain->getName())
                ->setDomainRef($domainId)->setData($data)
                ->setClientRef($domain->getOwner()->getId())->save();
        
        return true;
    }
    
}
