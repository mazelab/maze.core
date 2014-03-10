<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Domain extends Core_Model_ValueObject
{
    
    /**
     * flag to determine if search index should be rebuild after save operation
     * 
     * @var boolean
     */
    protected $_rebuildSearchIndex;

    /**
     * message when save failed
     */
    CONST ERROR_SAVING= 'Something went wrong while saving domain %1$s';

    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Domain
     */
    protected function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getDomain();
    }

    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getDomain($this->getId());
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedData)
    {
        $id = $this->_getProvider()->saveDomain($unmappedData, $this->getId());
        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getName());
            return false;
        }

        $this->_setId($id);
        if($this->_rebuildSearchIndex) {
            $this->_rebuildSearchIndex = false;
            Core_Model_DiFactory::getIndexManager()->setDomain($id);
        }
        
        return $id;
    }
    
    /**
     * adds a additional field
     * 
     * @param string $key
     * @param string $value
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($key, $value)
    {
        if (!$this->getId() || !is_string($key) || !is_string($value)) {
            return false;
        }

        $additionalField = array(
            "label" => $key,
            "value" => $value
        );

        $this->setData(array('additionalFields' => array(md5($key) => $additionalField)));
        if (!$this->save()){
            return false;
        }

        return md5($key);
    }
    
    /**
     * adds a certain service in data backend
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function addService($service)
    {
        if (!$this->getId()) {
            return false;
        }
        
        if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($service))) {
            return false;
        }
        
        $dataSet = array(
            'services' => array(
                $service->getName() => array(
                    'name' => $service->getName(),
                    'label' => $service->getLabel()
                )
            )
        );
        
        return $this->setData($dataSet)->save();
    }
    
    /**
     * deletes a certain additional field from this domain in the data backend
     * 
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($key)
    {
        if (!$this->getId() || !is_string($key)) {
            return false;
        }
        
        if(!$this->getData('additionalFields/' . $key)) {
            return true;
        }
        
        return $this->unsetProperty('additionalFields/' . $key)->save();
    }
    
    /**
     * returns node name from data set
     * 
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
    
    /**
     * returns the owner of this domain
     * 
     * @return Core_Model_ValueObject_Client|null
     */
    public function getOwner()
    {
        if (!$this->getData('owner')) {
            return null;
        }

        return Core_Model_DiFactory::getClientManager()->getClient($this->getData('owner'));
    }
    
    /**
     * returns all client services
     * 
     * @return array
     */
    public function getServices()
    {
        $services = array();
        
        if(!is_array($this->getData('services'))) {
            return array();
        }
        
        foreach(array_keys($this->getData('services')) as $serviceName) {
            if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName))) {
                continue;
            }
            
            $services[$service->getName()] = $service->getModuleConfig();
        }
        
        return $services;
    }
    
    /**
     * checks if the given service is allready registered
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function hasService($service)
    {
        $services = $this->getServices();

        if (key_exists($service, $services)) {
            return true;
        }

        return false;
    }
    
    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return Core_Model_ValueObject_Domain
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (key_exists('name', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return $this;
    }
    
}
