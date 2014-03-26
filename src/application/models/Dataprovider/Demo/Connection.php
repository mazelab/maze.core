<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Connection
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Connection 
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
        return true;
    }
    
}
