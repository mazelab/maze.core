<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Service_Interface_Composer
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Service_Interface_Composer
{
    
    /**
     * call composer install on given path
     * 
     * @param string $composerPath
     * @return boolean
     */
    public function install($composerPath);
    
    /**
     * updates all or a certain composer project
     * 
     * @param string $composerPath
     * @param string $projectName
     * @return boolean
     */
    public function update($composerPath, $projectName = null);
    
    /**
     * validates a composer file on given path
     * 
     * @param string $composerPath
     * @return boolean
     */
    public function validate($composerPath);
    
}
