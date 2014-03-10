<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Connection
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Connection
{
    
    /**
     * returns the connection status of the database
     * 
     * @return boolean connection status
     */
    public function status();
    
}
