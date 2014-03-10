<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Module
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Module
{
    
    /**
     * deletes a certain module
     * 
     * @param string $moduleName name of the module
     * @return boolean
     */
    public function deleteModule($moduleName);
    
    /**
     * returns all modules without the installed flag
     * 
     * @return array
     */
    public function getAvailableModules();
    
    /**
     * returns certain module data
     * 
     * @param string $moduleName name of the module
     * @return array
     */
    public function getModule($moduleName);

    /**
     * returns all modules which can be updated
     * 
     * @return array
     */
    public function getUpdateableModules();

    /**
     * saves the given module dataset
     * 
     * @param string $moduleName name of the module
     * @param array $data
     * @return boolean
     */
    public function saveModule($moduleName, $data);
    
}
