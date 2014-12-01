<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Translation
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Translation extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * init translation config from data backend
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if($request->getControllerName() === 'error') {
            return false;
        }

        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered("Zend_Translate")){
            $config = Core_Model_DiFactory::getConfig();
            $translate = $registry->get("Zend_Translate");
            $translate->getAdapter()->setLocale($config->getData("locale"));
        }
    }
    
}