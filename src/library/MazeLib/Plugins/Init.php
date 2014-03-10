<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Init
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Init extends Zend_Controller_Plugin_Abstract
{
    
    /**
     * @var Zend_Auth
     */
    protected $_auth;
    
    /**
     * @var MazeLib_Acl_Acl
     */
    protected $_acl;
    
    /**
     * @var string
     */
    protected $_role;

    /**
     * database connection status
     *
     * @var boolean
     */
    protected $_connection = false;

    /**
     * default role for acl
     */
    CONST ROLE_DEFAULT = 'guest';
    
    /**
     * role for unregistered api requests
     */
    CONST ROLE_API_UNREGISTERED = 'api_unregistered';
    
    /**
     * role fir registered api requests
     */
    CONST ROLE_API_REGISTERED = 'api_registered';
    
    /**
     * exception message when database connection check failed
     */
    CONST EXCEPTION_DATABASE_CONNECTION_FAILED = "Error Establishing a Database Connection";
    
    public function __construct()
    {
        $this->_auth = Zend_Auth::getInstance();
    }
    
    /**
     * return logger instance
     * 
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }

    /**
     * register mazeConfig in zend_Registry and sets locale
     * 
     * called by dispatchLoopStartup()
     */
    protected function _initMazeConfig()
    {
        $config = Core_Model_DiFactory::newConfig();
        if ($this->_connection){
            $config = Core_Model_DiFactory::getConfig();
        }

        Zend_Registry::getInstance()->set("mazeConfig", $config);
    }
    
    /**
     * initialize allready bootstraped modules for usage in maze
     */
    protected function _initModules()
    {
        $front = Zend_Controller_Front::getInstance();
        $defaultModule = $front->getDefaultModule();

        foreach ($front->getControllerDirectory() as $module => $path) {
            if ($module == $defaultModule) {
                continue;
            }
          
            // init module
            $configPath = dirname($path) . DIRECTORY_SEPARATOR . "module.ini";
            if (file_exists($configPath)) {
                Core_Model_DiFactory::getModuleRegistry()->registerModule($configPath);
            }
        }
    }

    /**
     * set ui language
     * 
     * called by dispatchLoopStartup()
     */
    protected function _initTranslation()
    {
        $registry = Zend_Registry::getInstance();
        if ($this->_connection && $registry->isRegistered("Zend_Translate")){
            $config = Core_Model_DiFactory::getConfig();
            $translate = $registry->get("Zend_Translate");
            $translate->getAdapter()->setLocale($config->getData("locale"));
        }
    }

    /**
     * connection test to the database server
     * 
     * called by routeShutdown()
     * 
     * @param  Zend_Controller_Request_Abstract $request
     * @throws Core_Model_Dataprovider_Exception
     * @return boolean
     */
    protected function _initCheckDataprovider(Zend_Controller_Request_Abstract $request)
    {
        $this->_connection = Core_Model_Dataprovider_DiFactory::getConnection()->status();

        if (!$this->_connection && $request->getControllerName() != "install"){
            throw new Exception(self::EXCEPTION_DATABASE_CONNECTION_FAILED);
        }
    }

    /**
     * authentication
     * 
     * called by routeShutdown()
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _initAuthenticate(Zend_Controller_Request_Abstract $request)
    {
        $this->_acl = Zend_Registry::getInstance()->get('MazeLib_Acl_Builder')->getAcl();
        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            if(!$request->getHeader('X-Requested-With') == 'Maze') {
                $this->_role = self::ROLE_DEFAULT;
            } else {
                $this->_initApiRequest($request);
            }
        } else {
            $user = Zend_Auth::getInstance()->getIdentity();
            $this->_role = $user["group"];
        }
        
        // authenticate
        if($module) {
            $resource = $module . '_' . $controller;
        } else {
            $resource = $controller;
        }
        
        if ($this->_acl->isAllowed($this->_role, $resource, $action)) {
            // set userdata in view
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $view->identity = Zend_Auth::getInstance()->getIdentity();
        } else {
            $this->_response->setHttpResponseCode(401);
            $this->_request->setModuleName('core');
            $this->_request->setControllerName('login');
            $this->_request->setActionName('login');
        }
        
        $this->_initAclNavigation();
    }
    
    /**
     * inits api request
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _initApiRequest(Zend_Controller_Request_Abstract $request)
    {
        if(($apiKey = $request->getHeader('X-Maze-Node-Api'))) {
            $nodeManager = Core_Model_DiFactory::getNodeManager();
            
            if(!($node = $nodeManager->getNodeByApiKey($apiKey))) {
                Core_Model_DiFactory::getApiManager()->logUnregistredApiRequest($request);
                
                $this->_role = self::ROLE_API_UNREGISTERED;
            } elseif ($node->getData('ipAddress') !== $request->getHeader('X-Maze-Node-Ip') ||
                    $node->getName() !== $request->getHeader('X-Maze-Node-Name')) {
                Core_Model_DiFactory::getApiManager()->logCorruptedApiRequest($request);
                
                $this->_role = self::ROLE_API_UNREGISTERED;
            } else {
                $this->_role = self::ROLE_API_REGISTERED;
            }
            
        } else {
            $this->_role = self::ROLE_API_UNREGISTERED;
        }
    }
    
    /**
     * sets navigation in view according to acl
     */
    protected function _initAclNavigation()
    {
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();

        $view->navigation()->setAcl($this->_acl)->setRole($this->_role);
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->_initCheckDataprovider($request);
        $this->_initAuthenticate($request);
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_connection){
            $this->_initMazeConfig();
            $this->_initTranslation();
            $this->_initModules();
        }
    }
    
}