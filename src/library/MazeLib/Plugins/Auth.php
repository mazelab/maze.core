<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Auth
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Auth extends Zend_Controller_Plugin_Abstract
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
    
    public function __construct()
    {
        $this->_auth = Zend_Auth::getInstance();
    }

    /**
     * exit app with the given code
     *
     * exits php!
     *
     * @param int $code
     */
    protected function _exitWithCode($code = 500)
    {
        $this->getResponse()->setHttpResponseCode($code);
        $this->getResponse()->sendResponse();
        exit;
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
     * authentication for api token requests
     *
     * serve better return codes
     * ignores Zend_Auth instance because no session is needed
     *
     * called by routeShutdown()
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _initAuthenticateToken(Zend_Controller_Request_Abstract $request)
    {
        $this->_acl = Zend_Registry::getInstance()->get('MazeLib_Acl_Builder')->getAcl();
        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();

        $errorCode = '';
        if(!Zend_Registry::isRegistered('config')) {
            $this->_exitWithCode(500);
        }

        $config = Zend_Registry::get('config');
        $probe = $request->getHeader('X-Authorization-Token');
        if(!$errorCode && (!$config->api || !($token = $config->api->token) || $token !== $probe)) {
            $this->_exitWithCode(403);
        }

        $resource = $module ? $module . '_' . $controller : $controller;
        if ($errorCode || !$this->_acl->isAllowed('admin', $resource, $action)) {
            $this->_exitWithCode(403);
        }
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
        if($request->getControllerName() === 'error') {
            return false;
        }

        if($request->getHeader('X-Authorization-Token')) {
            $this->_initAuthenticateToken($request);
        } else {
            $this->_initAuthenticate($request);
        }
    }

}