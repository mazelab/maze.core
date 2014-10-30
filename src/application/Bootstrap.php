<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Bootstrap
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initAcl()
    {
        $acl = new MazeLib_Acl_Builder();
        $aclPath = __DIR__ . '/configs/acl.ini';
        
        if (file_exists($aclPath)) {
            $acl->addConfig(new Zend_Config_Ini($aclPath));
        }
        
        Zend_Registry::set('MazeLib_Acl_Builder', $acl);
    }
    
    /**
     * @return Zend_Config
     */
    protected function _initConfig()
    {
        $config = $this->getOptions();
        
        if (file_exists(APPLICATION_PATH . '/../data/configs/server.ini')) {
            $serverConfig = new Zend_Config_Ini(APPLICATION_PATH . '/../data/configs/server.ini');
            $config = array_replace_recursive($config, $serverConfig->toArray());
        }
        
        $this->setOptions($config);

        //adding config to zend_Registry
        Zend_Registry::set('config', new Zend_Config($config, true));

        return $config;
    }
    
    protected function _initNavigation()
    {
        $naviPath = __DIR__ . '/configs/navigation.ini';

        $this->bootstrap('view');
        
        if (file_exists($naviPath)) {
            $view = $this->getResource('view');

            $config = new Zend_Config_Ini($naviPath);
            $view->navigation()->addPages($config);
        }
        
        return $view->navigation();
    } 
    
    protected function _initPutHandler()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Zend_Controller_Plugin_PutHandler());
    }

    /**
     * Set the router config
     * If a routes.ini exists in this modul, it will be initialized
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function _initRouter()
    {
        // Gets a router object from front controller
        $router = Zend_Controller_Front::getInstance()->getRouter();

        // if routes.ini exists then use it
        if (file_exists(APPLICATION_PATH . '/configs/routes.ini')) {
            $routingFile = APPLICATION_PATH . '/configs/routes.ini';
            $router->addConfig(new Zend_Config_Ini($routingFile, $this->getEnvironment()), 'routes');
        }

        return $router;
    }
    
    protected function _initView()
    {
        $resources = $this->getOption('resources');
        $options = array();
        
        if (isset($resources['view'])) {
            $options = $resources['view'];
        }
        
        $view = new MazeLib_View_Autoescape($options);
        if (isset($options['doctype'])) {
            $view->doctype()->setDoctype(strtoupper($options['doctype']));
            
            if (isset($options['charset']) && $view->doctype()->isHtml5()) {
                $view->headMeta()->setCharset($options['charset']);
            }
        }
        
        if (isset($options['contentType'])) {
            $view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
        }

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        return $view;
    }

}
