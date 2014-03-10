<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Config
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Config
{
    
    /**
     * returns config
     * 
     * @return array
     */
    public function getConfig();
    
    /**
     * saves the given config dataset
     * 
     * @param array $data
     * @return boolean
     */
    public function saveConfig(array $data);
    
}
