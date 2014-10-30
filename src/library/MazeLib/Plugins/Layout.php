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
     */
    protected function _includeModuleLayoutFiles()
    {
        $modules = Core_Model_DiFactory::getModuleManager()->getInstalledModules();
        $view  = Zend_Layout::getMvcInstance()->getView();

        foreach($modules as $module) {
            if(isset($module['admin']['scripts']['prepend']) && ($files = $module['admin']['scripts']['prepend']) &&
                    is_array($files)) {
                foreach($files as $prepend) {
                    $view->headScript()->prependFile($view->baseUrl() . $prepend);
                }
            }
            if(isset($module['admin']['css']['prepend']) && ($files = $module['admin']['css']['prepend']) &&
                is_array($files)) {
                foreach($files as $append) {
                    $view->headLink()->prependStylesheet($view->baseUrl() . $append);
                }
            }
            if(isset($module['admin']['scripts']['append']) && ($files = $module['admin']['scripts']['append']) &&
                    is_array($files)) {
                foreach($files as $append) {
                    $view->headScript()->appendFile($view->baseUrl() . $append);
                }
            }
            if(isset($module['admin']['css']['append']) && ($files = $module['admin']['css']['append']) &&
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
            default:
                $script = "client/layout";
                break;
        }

        Zend_Layout::getMvcInstance()->setLayout($script);
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
        $this->_initLayout();

        // include angular relevant implementation
        if ($this->_role === Core_Model_UserManager::GROUP_ADMIN) {
            $this->_includeModuleLayoutFiles();
            $this->_setAngularModuleString();
        }
    }

}