<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Layout
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Layout extends Zend_Controller_Plugin_Abstract
{

    /**
     * user role
     *
     * @var string|null
     */
    protected $_role;

    /**
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    public function __construct()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();

        if($identity && isset($identity['group']) && $identity['group']) {
            $this->_role = $identity['group'];
        }
    }

    /**
     * includes files in layout from module definitions
     *
     * @todo refactoring
     * @param string $group
     *
     */
    protected function _includeModuleLayoutFiles($group = null)
    {
        if (!$group || empty($group)) {
            return;
        }

        $modules = Core_Model_DiFactory::getModuleManager()->getInstalledModules();
        $view  = Zend_Layout::getMvcInstance()->getView();

        foreach($modules as $module) {
            if(isset($module[$group]['scripts']['prepend']) && ($files = $module[$group]['scripts']['prepend']) &&
                    is_array($files)) {
                foreach($files as $prepend) {
                    $view->headScript()->prependFile($view->baseUrl() . $prepend);
                }
            }
            if(isset($module[$group]['css']['prepend']) && ($files = $module[$group]['css']['prepend']) &&
                is_array($files)) {
                foreach($files as $append) {
                    $view->headLink()->prependStylesheet($view->baseUrl() . $append);
                }
            }
            if(isset($module[$group]['scripts']['append']) && ($files = $module[$group]['scripts']['append']) &&
                    is_array($files)) {
                foreach($files as $append) {
                    $view->headScript()->appendFile($view->baseUrl() . $append);
                }
            }
            if(isset($module[$group]['css']['append']) && ($files = $module[$group]['css']['append']) &&
                    is_array($files)) {
                foreach($files as $append) {
                    $view->headLink()->appendStylesheet($view->baseUrl() . $append);
                }
            }
        }
    }

    /**
     * set layout depending on the user lvl
     */
    protected function _initLayout()
    {
        switch ($this->_role) {
            case "admin":
                $script = "admin/layout";
                break;
            case "client":
                $script = "client/layout";
                break;
        }

        if(isset($script)) {
            Zend_Layout::getMvcInstance()->setLayout($script);
        }

        $mazeConfig = Core_Model_DiFactory::getConfig();
        $view  = Zend_Layout::getMvcInstance()->getView();

        $view->assign("company", (string) $mazeConfig->getData("company"));
    }

    /**
     * builds angular modules string from module definitions and sets it in the layout
     *
     * @todo refactoring
     */
    protected function _setAngularModuleString()
    {
        $modules = Core_Model_DiFactory::getModuleManager()->getInstalledModules();
        $view  = Zend_Layout::getMvcInstance()->getView();

        $angularModules = array();
        foreach($modules as $module) {
            if(isset($module['admin']['angular']['modules']) && ($modules = $module['admin']['angular']['modules'])
                    && is_array($modules)) {
                foreach($modules as $module) {
                    if(!is_string($module)) {
                        continue;
                    }
                    array_push($angularModules, $module);
                }
            }
        }

        $modules = null;
        foreach(array_keys(array_flip($angularModules)) as $module) {
            if($modules) {
                $modules = $modules . ",";
            }
            $modules = $modules . "\"". $module ."\"";
        }

        $view->angularModules = $modules;
    }

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // skip further layout initializing when api uri or error controller
        if((($requestUri = $request->getRequestUri()) && preg_match('/^\/api\//', $requestUri))
                || $request->getControllerName() === 'error') {
            $request->setParam('format', 'json');
            return false;
        }

        $this->_initLayout();

        // include angular relevant implementation
        if ($this->_role === Core_Model_UserManager::GROUP_ADMIN) {
            $this->_includeModuleLayoutFiles(Core_Model_UserManager::GROUP_ADMIN);
            $this->_setAngularModuleString();
        } else if ($this->_role === Core_Model_UserManager::GROUP_CLIENT &&
                !$request->getModuleName() && $request->getModuleName() !== "Core") {
            $this->_includeModuleLayoutFiles(Core_Model_UserManager::GROUP_CLIENT);
        }
    }

}
