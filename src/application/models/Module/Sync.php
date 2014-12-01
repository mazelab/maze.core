<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Sync
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Sync
{

    /**
     * error message if module declaration is invalid
     */
    CONST ERROR_INVALID_DECLARATION = 'Invalid module declaration';

    /**
     * error message if no sync target is provided
     */
    CONST ERROR_NO_TARGET = 'No target provided for module sync';

    /**
     * file path that contains the module data
     * 
     * @var string
     */
    protected $_moduleSyncUrl;

    /**
     * init maze configuration
     */
    public function __construct()
    {
        if(($config = Zend_Registry::getInstance()->get('config')) && $config->maze) {
            $this->setModuleSyncUrl($config->maze->modulesUpdateUrl);
        }
    }
    
    /**
     * initiates sync request and transforms the return into array
     * 
     * @return array
     */
    protected function _getModuleUpdates()
    {
        $modulesJson = $this->_loadJson();
        
        try{
            $modules = Zend_Json::decode($modulesJson);
        } catch (Exception $e) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_INVALID_DECLARATION);
            return array();
        }

        if(!is_array($modules)) {
            return array();
        }
        
        return $modules;
    }
    
    /**
     * loads module update data from provided source and returns its content as array
     * 
     * @return array
     */
    public function _loadJson()
    {
        $json = '';

        if(Zend_Uri::check($this->getModuleSyncTarget())) {
            $zendHttp = new Zend_Http_Client($this->getModuleSyncTarget());
            $response = $zendHttp->request();
            $json = $response->getBody();
        } elseif (file_exists($this->getModuleSyncTarget())) {
            $configJson = new Zend_Config_Json($this->getModuleSyncTarget());
            $jsonWriter = new Zend_Config_Writer_Json();
            
            $jsonWriter->setConfig($configJson);
            $json = $jsonWriter->render();
        }

        return $json;
    }
    
    /**
     * use sync module script for async module data sync
     * 
     * @return boolean
     */
    protected function _syncScript()
    {
        if(!($bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap'))) {
            return false;
        }
        
        $status = 1;
        $output = '';
        $command = "php " . APPLICATION_PATH . '/../data/scripts/moduleUpdate.php';
        $command .= ' ' . $bootstrap->getEnvironment();
        exec(escapeshellcmd($command) . " > /dev/null &", $output, $status);

        if($status != 0) {
            return false;
        }
        
        return true;
    }

    /**
     * returns module sync url
     * 
     * @return string
     */
    public function getModuleSyncTarget()
    {
        return $this->_moduleSyncUrl;
    }
    
    /**
     * sets module sync url
     * 
     * @param string $moduleLibUrl
     * @return string
     */
    public function setModuleSyncUrl($moduleLibUrl)
    {
        $this->_moduleSyncUrl = $moduleLibUrl;
    }
    
    /**
     * synchronize module lib information
     * 
     * @return boolean
     */
    public function sync()
    {
        if(!$this->getModuleSyncTarget()) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_NO_TARGET);
            return false;
        }
        if(!($modules = $this->_getModuleUpdates())) {
            return false;
        }

        $moduleManager = Core_Model_DiFactory::getModuleManager();
        foreach($modules as $moduleData) {
            if(!array_key_exists('name', $moduleData)) {
                continue;
            }

            if(!($module = $moduleManager->getModule($moduleData['name']))) {
                $moduleManager->addModule($moduleData);
            } else {
                $module->syncModuleUpdate($moduleData);
            }
        }

        Core_Model_DiFactory::getConfig()->setData(array('lastModuleSync' => time()))->save();

        return true;
    }
    
    /**
     * sync module data on a daily basis
     * 
     * @return boolean
     */
    public function syncDaily()
    {
        $mazeConfig = Core_Model_DiFactory::getConfig();
        $date = new Zend_Date($mazeConfig->getData('lastModuleSync'));
        
        if(!$mazeConfig->getData('lastModuleSync') || !$date->isToday()) {
            $mazeConfig->setData(array('lastModuleSync' => time()))->save();
            
            return $this->_syncScript();
        }
        
        return true;
    }
    
}
