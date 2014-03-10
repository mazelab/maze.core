<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Config
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Config
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
    implements Core_Model_Dataprovider_Interface_Config
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'config';

    /**
     * returns config
     * 
     * @return array
     */
    public function getConfig()
    {
        return array();
    }

    /**
     * saves the given config dataset
     * 
     * @param array $data
     * @return boolean
     */
    public function saveConfig(array $data)
    {
        return false;
    }
    
}
