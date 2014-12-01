<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Modules
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Modules extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * register maze modules
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
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
    
}