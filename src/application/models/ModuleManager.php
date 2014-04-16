<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ModuleManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ModuleManager
{
    
    /**
     * error message when composer install failed
     */
    CONST ERROR_COMPOSER_INSTALL_FAILED = 'Composer install failed!';
    
    /**
     * error message when module add failed because of an invalid module structure
     */
    CONST ERROR_MODULE_ADD_INVALID = 'Couldn\'t add module because of invalid context.';
    
    /**
     * error message when module removing failed
     */
    CONST ERROR_MODULE_REMOVING_FAILED = 'Couldn\'t remove module files.';
    
    /**
     * generall error if module update failed
     */
    CONST ERROR_MODULE_UPDATE_FAILED = 'Couldn\'t update module';
    
    /**
     * get composer instance
     * 
     * @var Core_Model_Module_Composer
     */
    protected $_composer;
    
    /**
     * path of the composer file
     * 
     * @var string
     */
    protected $_modulesPath;
    
    /**
     * init maze module path
     */
    public function __construct()
    {
        if(Zend_Registry::isRegistered('config')) {
            $config = Zend_Registry::get('config');
            if(isset($config->resources->maze->moduleDirectory)) {
                $this->setModulePath($config->resources->maze->moduleDirectory);
            }
        }
    }
    
    /**
     * returns a certain module instance if registered
     * 
     * @param string $moduleName
     * @return Core_Model_ValueObject_Module|null null if not registered
     */
    protected function _getRegisteredModule($moduleName)
    {
        if(!$this->isModuleRegistered($moduleName)) {
            return null;
        }
        
        return Core_Model_DiFactory::getModule($moduleName);
    }
    
    /**
     * loads and registers a certain module instance
     * 
     * @param string $moduleName
     * @return boolean
     */
    protected function _loadModule($moduleName)
    {
        if(!$moduleName) {
            return null;
        }
        
        $data = $this->getProvider()->getModule($moduleName);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerModule($moduleName, $data);
    }
    
    /**
     * adds an additional field to the given installed module
     * 
     * @param string $moduleName
     * @param array $data
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($moduleName, $data)
    {
        if(!($module = $this->getModule($moduleName))
                || !key_exists("additionalKey", $data) || !key_exists("additionalValue", $data)) {
            return false;
        }

        if (!($additionalId = $module->addAdditionalField($data['additionalKey'], $data['additionalValue'])) ||
                !$module->save()) {
            return false;
        };

        return $additionalId;
    }
    
    /**
     * adds module with given module data into the data backend
     * 
     * @param array $moduleData
     * @return boolean
     */
    public function addModule(array $moduleData)
    {
        $form = new Core_Form_Module();
        
        if(!$form->setRepositorySubForm()->isValid($moduleData)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_MODULE_ADD_INVALID);
            return false;
        }
        
        $module = Core_Model_DiFactory::newModule($form->getValue('name'));
        if(!$module->setLoaded(true)->setData($moduleData)->save()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * deinstall given module in application and composer
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function deinstallModule($moduleName)
    {
        if(!($module = $this->getModule($moduleName)) || !$module->isInstalled()) {
            return false;
        }

        if(!Core_Model_DiFactory::getClientManager()->removeClientsService($moduleName) ||
                !Core_Model_DiFactory::getDomainManager()->removeDomainsService($moduleName) ||
                !Core_Model_DiFactory::getNodeManager()->removeNodesService($moduleName)) {
            return false;
        }
        if(!Core_Model_DiFactory::getModuleApi()->deinstall($moduleName)) {
            return false;
        }

        if(!$this->getComposer()->removeModule($moduleName)) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_MODULE_REMOVING_FAILED);
            return false;
        }
        
        if($module->getData('updateable') && $module->getData('update')) {
            $module->setData($module->getData('update'));
        }
                
        return $module->unsetProperty('update')->unsetProperty('updateable')->unsetProperty('installed')->save();
    }

    /**
     * deletes a certain additional field from this module in the data backend
     *
     * @param  string $moduleName
     * @param  mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($moduleName, $key)
    {
        if(!($module = $this->getModule($moduleName))) {
            return false;
        }

        if (!$module->deleteAdditionalField($key) || !$module->save())  {
            return false;
        }

        return true;
    }

    /**
     * removes module from data backend
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function deleteModule($moduleName)
    {
        if(!($module = $this->getModule($moduleName))) {
            return false;
        }
        
        Core_Model_DiFactory::getModuleManager()->unregisterModule($moduleName);
        Core_Model_DiFactory::getModuleRegistry()->unregisterModule($moduleName);
        
        return $this->getProvider()->deleteModule($moduleName);
    }
    
    /**
     * returns an array of available modules
     * 
     * @return array
     */
    public function getAvailableModules()
    {
        if(!($modules = $this->getProvider()->getAvailableModules()) || !is_array($modules)) {
            return array();
        }

        return $modules;
    }
    
    /**
     * get actual composer instance
     * 
     * @return Core_Model_Module_Composer
     */
    public function getComposer()
    {
        if(!$this->_composer && $this->getModulePath()) {
            $this->_composer = Core_Model_DiFactory::newModuleComposer($this->getModulePath());
        }
        
        return $this->_composer;
    }
    
    /**
     * returns an array of all installed modules
     * 
     * @return array
     */
    public function getInstalledModules()
    {
        $modules = array();
        
        if(!($installedModules = Core_Model_DiFactory::getModuleRegistry()->getModules()) || empty($installedModules)) {
            return array();
        }
        
        foreach($installedModules as $moduleName => $module) {
            $modules[$moduleName] = $module->getModuleConfig();
        }
        
        return $modules;
    }
    
    /**
     * returns a certain module by id as object
     * 
     * @param string $moduleName
     * @return Core_Model_ValueObject_Module|null
     */
    public function getModule($moduleName)
    {
        if(!$this->isModuleRegistered($moduleName)) {
            $this->_loadModule($moduleName);
        }
        
        return $this->_getRegisteredModule($moduleName);
    }
    
    /**
     * returns a certain module by id as array
     * 
     * @param string $moduleName
     * @return array
     */
    public function getModuleAsArray($moduleName)
    {
        if(!$this->isModuleRegistered($moduleName)) {
            $this->_loadModule($moduleName);
        }
        
        if(!($module = $this->_getRegisteredModule($moduleName))) {
            return array();
        }
        
        return $module->getData();
    }
    
    /**
     * returns composer file path
     * 
     * @return string
     */
    public function getModulePath()
    {
        return $this->_modulesPath;
    }
    
    /**
     * get provider
     * 
     * @return Core_Model_Dataprovider_Interface_Module
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getModule();
    }
    
    /**
     * gets all modules which can be updated
     * 
     * @return array
     */
    public function getUpdateableModules()
    {
        if(!($modules = $this->getProvider()->getUpdateableModules()) || !is_array($modules)) {
            return array();
        }
        
        return $modules;
    }
    
    /**
     * install a certain module
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function installModule($moduleName)
    {
        if(!($module = $this->getModule($moduleName)) || $module->isInstalled()) {
            return false;
        }
        if(!$this->getComposer()->setModule($moduleName)) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_COMPOSER_INSTALL_FAILED);
            return false;
        }
        
        return $module->setData(array('installed' => true))->unsetProperty('updateable')->save();
    }
    
    /**
     * checks if a certain module instance is allready registered
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function isModuleRegistered($moduleName)
    {
        if(Core_Model_DiFactory::isModuleRegistered($moduleName)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a module instance
     * 
     * overwrites existing instances
     * 
     * @param string $moduleName
     * @param mixed $context array or Core_Model_ValueObject_Module
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerModule($moduleName, $context, $setLoadedFlag = true)
    {
        $module = null;
        
        if(is_array($context)) {
            $module = Core_Model_DiFactory::newModule($moduleName);
            
            if($setLoadedFlag) {
                $module->setLoaded(true);
            }
            
            $module->getBean()->setBean($context);
        } elseif($context instanceof Core_Model_ValueObject_Module) {
            $module = $context;
        }
        
        if(!$module) {
            return false;
        }
        
        Core_Model_DiFactory::registerModule($moduleName, $module);
        
        return true;
    }
    
    /**
     * set composer instance
     * 
     * @param Core_Model_Module_Composer $composer
     * @return Core_Model_ModuleManager
     */
    public function setComposer(Core_Model_Module_Composer $composer = null)
    {
        $this->_composer = $composer;
        
        return $this;
    }
    
    /**
     * sets composer path
     * 
     * @param string $modulePath
     */
    public function setModulePath($modulePath)
    {
        if(is_string($modulePath)) {
            $this->_modulesPath = $modulePath;
            $this->setComposer();
        }
    }
    
    /**
     * unregisters a certain module instance
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function unregisterModule($moduleName)
    {
        if(!$this->_getRegisteredModule($moduleName)) {
            return true;
        }
        
        Core_Model_DiFactory::unregisterModule($moduleName);
    }
    
    /**
     * updates the given module with the newest version
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function updateModule($moduleName)
    {
        if(!($module = Core_Model_DiFactory::getModuleRegistry()->getModule($moduleName)) || 
                !$module->getData('update')) {
            return false;
        }
        if(!$this->getComposer()->setModule($moduleName)) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_MODULE_UPDATE_FAILED);
            return false;
        }
        
        // set new version data into main data set
        $module->setData($module->getData('update'));
        return $module->unsetProperty('update')->unsetProperty('updateable')->save();
    }
    
    /**
     * updates additional fields of a certain module
     * 
     * @param string $moduleName
     * @param array $data additional fields data
     * @return boolean
     */
    public function updateModuleAdditionalFields($moduleName, array $data)
    {
        if (!($module = $this->getModule($moduleName))){
            return false;
        }

        foreach ($data as $id => $value) {
           if (empty($value) || trim($value) == "") {
               $module->unsetProperty('additionalFields/' . $id);
           } else {
               $module->setProperty("additionalFields/$id/value", $value);
           }
        }
        
        return $module->save();
    }
    
}
