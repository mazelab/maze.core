<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Module
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Module
    extends Core_Model_ValueObject
{
    
    /**
     * contains module base config
     *
     * @var MazeLib_Bean
     */
    protected $_config;
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Module
     */
    protected function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getModule();
    }
    
    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        $data = $this->_getProvider()->getModule($this->getName());

        // if unsynched local module than set module config values as data
        $moduleConfig = $this->getModuleConfig();
        if(empty($data) && !empty($moduleConfig)) {
            $data['name'] = $this->getModuleConfig('name');
            $data['label'] = $this->getModuleConfig('label');
            $data['vendor'] = $this->getModuleConfig('vendor');
            $data['description'] = $this->getModuleConfig('description');
            $data['repository'] = $this->getModuleConfig('repository');
            
            $data['authors'] = $this->getModuleConfig('authors');
            $data['wiki'] = $this->getModuleConfig('wiki');
            
            $data['installed'] = true;
        }
        
        return $data;
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
     * adds given data set into a certain client configuration
     * 
     * @param string $clientId
     * @param array $data
     * @return Core_Model_ValueObject_Module
     */
    public function addClientConfig($clientId, $data)
    {
        $dataSet = array();
        
        if(is_string($clientId) && is_array($data)) {
            $dataSet['config']['clients'][$clientId] = $data;
            $this->setData($dataSet);
        }

        return $this;
    }
    
    /**
     * adds config entry for top level config
     * 
     * @param array $data
     * @return Core_Model_ValueObject_Module
     */
    public function addConfig(array $data)
    {
        return $this->setData(array('config' => $data));
    }
    
    /**
     * adds given data set into a certain domain configuration
     * 
     * @param string $domainId
     * @param array $data
     * @return Core_Model_ValueObject_Module
     */
    public function addDomainConfig($domainId, $data)
    {
        $dataSet = array();
        
        if(is_string($domainId) && is_array($data)) {
            $dataSet['config']['domains'][$domainId] = $data;
            $this->setData($dataSet);
        }

        return $this;
    }
    
    /**
     * adds given data set into a certain node configuration
     * 
     * @param string $nodeId
     * @param array $data
     * @return Core_Model_ValueObject_Module
     */
    public function addNodeConfig($nodeId, $data)
    {
        $dataSet = array();
        
        if(is_string($nodeId) && is_array($data)) {
            $dataSet['config']['nodes'][$nodeId] = $data;
            $this->setData($dataSet);
        }

        return $this;
    }
    
    /**
     * returns general or a certain client config
     * alias for getData('config/clients')
     * or getData("config/clients/$clientId")
     * 
     * @param string $clientId
     * @return array
     */
    public function getClientConfig($clientId = null)
    {
        if(!$clientId) {
            $config = $this->getData('config/clients');
        } else {
            $config = $this->getData("config/clients/$clientId");
        }
        
        if(!is_array($config)) {
            return array();
        }
        
        return $config;
    }
    
    /**
     * gets complete module configuration
     * alias for getData('config')
     * 
     * @return array
     */
    public function getConfig()
    {
        $config = $this->getData('config');
        if(!is_array($config)) {
            return array();
        }
        
        return $config;
    }
    
    /**
     * returns general or a certain domain config
     * alias for getData('config/domains')
     * or getData("config/domains/$domainId")
     * 
     * @param string $domainId
     * @return array
     */
    public function getDomainConfig($domainId = null)
    {
        if(!$domainId) {
            $config = $this->getData('config/domains');
        } else {
            $config = $this->getData("config/domains/$domainId");
        }
        
        if(!is_array($config)) {
            return array();
        }
        
        return $config;
    }
    
    /**
     * return module label
     * 
     * @return string|null
     */
    public function getLabel()
    {
        $config = $this->getModuleConfig();
        if(!is_array($config) || !key_exists('label', $config)) {
            return null;
        }
        
        return $config['label'];
    }
    
    /**
     * alias for getId()
     * 
     * @return string|null
     */
    public function getName()
    {
        return $this->getId();
    }
    
    /**
     * returns general or a certain node config
     * alias for getData('config/nodes')
     * or getData("config/nodes/$nodeId")
     * 
     * @param string $nodeId
     * @return array
     */
    public function getNodeConfig($nodeId = null)
    {
        if(!$nodeId) {
            $config = $this->getData('config/nodes');
        } else {
            $config = $this->getData("config/nodes/$nodeId");
        }
        
        if(!is_array($config)) {
            return array();
        }
        
        return $config;
    }
    
    /**
     * returns module base configuration
     * 
     * @param string $propertyPath MazeLib_Bean property path
     * @return mixed
     */
    public function getModuleConfig($propertyPath = null)
    {
        if(!$this->_config) {
            return null;
        }
        
        if($propertyPath) {
            return $this->_config->getProperty($propertyPath);
        }
        
        return $this->_config->asDeepArray();
    }
    
    /**
     * returns installed state of module instance
     * 
     * @return boolean
     */
    public function isInstalled()
    {
        if($this->getData('installed') !== true) {
            return false;
        }
        
        return true;
    }

    /**
     * compares given version with existing version in order to check if 
     * given version is newer
     * 
     * @param string $version
     * @return boolean
     */
    public function isNewerVersion($version)
    {
        if(!($currentVersion = $this->getData('repository/version'))) {
            return false;
        }
        
        if($version == $currentVersion) {
            return false;
        }
        
        return version_compare($version, $currentVersion, '>');
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * calls _save
     * 
     * @return boolean
     */
    public function save()
    {
        if(!$this->getName()) {
            return false;
        }
        
        $unmappedData = $this->getBean()->asDeepArray(true);
        if(key_exists('_id', $unmappedData)) {
            unset($unmappedData['_id']);
        }

        if (!$this->_getProvider()->saveModule($this->getName(), $unmappedData)) {
            return false;
        }

        return true;
    }
    
    /**
     * sets module base configuration
     * 
     * @param array $config
     * @return Core_Model_ValueObject_Module
     */
    public function setModuleConfig(array $config)
    {
        $this->_config = new MazeLib_Bean($config);
        
        return $this;
    }
    
    /**
     * snychs given module Data with exising module data
     * 
     * @param array $moduleData
     * @return boolean
     */
    public function syncModuleUpdate($moduleData)
    {
        $form = new Core_Form_Module();
        if(!$form->setRepositorySubForm()->isValid($moduleData) ||
                $form->getValue('name') !== $this->getName()) {
            return false;
        }
        
        if(!$this->isInstalled()) {
            $this->setData($moduleData);
        } else if (!$this->getData('repository')) {
            $this->setData(array('repository' => $form->repository->getValues()));
        } else if($this->isNewerVersion($form->repository->getValue('version'))) {
            $this->setData(array('update' => $moduleData, 'updateable' => true));
        } else {
            return false;
        }
        
        return $this->save();
    }
    
        }
