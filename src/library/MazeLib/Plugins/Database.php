<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Database
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Database extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * check Database connectivity
     *
     * @param Zend_Controller_Request_Abstract $request
     * @throws Exception
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $connected = Core_Model_Dataprovider_DiFactory::getConnection()->status();

        if (!$connected && $request->getControllerName() != "install"){
            throw new Exception("Error Establishing a Database Connection");
        }
    }
    
}