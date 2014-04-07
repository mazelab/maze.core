<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Connection
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Connection 
    implements Core_Model_Dataprovider_Interface_Connection
{
    
    /**
     * returns the connection status of the database
     *
     * @param  Zend_Config $config
     * @return boolean connection status
     */
    public function status(Zend_Config $config = null)
    {
        if(!($mongo = Core_Model_DiFactory::newMongoDb($config))) {
            return false;
        }

        return $mongo->check();
    }
    
}
