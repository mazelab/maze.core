<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Registry
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Registry
{

    /**
     * exception message when module couldn't be added
     */
    CONST EXCEPTION_MODULE_ADD = 'Couldn\'t add module %1$s';
    
    /**
     * exception message when registering allready registered module
     */
    CONST EXCEPTION_MODULE_ALLREADY_REGISTERED = 'Module %1$s is allready registered';
    
    /**
     * exception message when module isn't properly implemented
     */
    CONST EXCEPTION_MODULE_INVALID_IMPLEMENTATION = 'Module Implementation is invalid';
    
    /**
     * @var Core_Model_Module_Registry
     */
    private static $_instance;
    
    /**
     * array that contains all initialized modules
     * 
     * @var array
     */
    protected $_modules;

    /**
     * loads module configuration from given path
     * 
     * @param string $configPath
     * @param array|null
     */
    protected function _loadConfig($configPath)
    {
        if (!file_exists($configPath) || !is_readable($configPath)) {
            return false;
        }
        
        $config = new Zend_Config_Ini($configPath);
        
        return $config->toArray();
    }
    
    /**
     * adds given module to the stack
     * 
     * @param Core_Model_ValueObject_Module $module
     * @return boolean
     */
    protected function _registerModule(Core_Model_ValueObject_Module $module)
    {
        if(!$module->getName()) {
            return false;
        }
        
        $this->_modules[$module->getName()] = true;
        
        Core_Model_DiFactory::getModuleManager()->registerModule($module->getName(), $module);
        
        return true;
    }
    
    /**
     * validates given module config
     * 
     * @param array $config
     * @return boolean
     */
    protected function _validateConfig(array $config)
    {
        $moduleForm = new Core_Form_Module();

        if(!$moduleForm->isValid($config)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * returns current instance
     * 
     * @return Core_Model_Module_Registry
     */
    public static function getInstance() 
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
 
        return self::$_instance;
    }
    
    /**
     * Retrieve a certain module
     * 
     * @param string $moduleName name of the module
     * @return 
     */
    public function getModule($moduleName)
    {
        if(!is_array($this->_modules) || !array_key_exists($moduleName, $this->_modules)) {
            return null;
        }
        
        return Core_Model_DiFactory::getModuleManager()->getModule($moduleName);
    }
    
    /**
     * return all modules
     *
     * @return array contains Core_Model_ValueObject_Module
     */
    public function getModules()
    {
        $modules = array();
        
        if(!is_array($this->_modules)) {
            return array();
        }
        
        foreach(array_keys($this->_modules) as $moduleName) {
            if(($module = Core_Model_DiFactory::getModuleManager()->getModule($moduleName))) {
                $modules[$moduleName] = $module;
            }
        }
     
        return $modules;
    }
    
    /**
     * registers new module
     * 
     * @param string $configPath path to module config
     * @throws Core_Model_Module_Exception
     * @return boolean
     */
    public function registerModule($configPath)
    {
        if(!($config = $this->_loadConfig($configPath)) || empty($config) ||
                !$this->_validateConfig($config)) {
            throw new Core_Model_Module_Exception(self::EXCEPTION_MODULE_INVALID_IMPLEMENTATION);
        }
        
        $moduleName = $config['name'];
        if($this->getModule($moduleName)) {
            throw new Core_Model_Module_Exception(
                vsprintf(self::EXCEPTION_MODULE_ALLREADY_REGISTERED, $moduleName)
            );            
        }
        
        $module = Core_Model_DiFactory::newModule($moduleName);
        $module->setModuleConfig($config);
        
        if(!$this->_registerModule($module)) {
            throw new Core_Model_Module_Exception(
                vsprintf(self::EXCEPTION_MODULE_ADD, $moduleName)
            );
        }
        
        return true;
    }
    
    /**
     * sets module registry instance
     * 
     * @param Core_Model_Module_Registry $registry
     */
    public static function setInstance(Core_Model_Module_Registry $registry = null) 
    {
        self::$_instance = $registry;
    }
    
    /**
     * removes module if registered
     * 
     * @param string $module name of the module
     * @return boolean
     */
    public function unregisterModule($module)
    {
        if(!is_array($this->_modules) || !array_key_exists($module, $this->_modules)) {
            return false;
        }
        
        unset($this->_modules[$module]);
        
        return true;
    }
    
}
