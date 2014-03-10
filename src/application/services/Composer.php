<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Service_Composer
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Service_Composer 
    implements Core_Service_Interface_Composer
{
    
    /**
     * call composer install on given path
     * 
     * @param string $composerPath
     * @return boolean
     */
    public function install($composerPath)
    {
        $composerPath = escapeshellarg($composerPath);
        $status = null;
        $output = null;
        exec("cd $composerPath && composer install", $output, $status);

        if($status != 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * updates all or a certain composer project
     * 
     * @param string $composerPath
     * @param string $projectName
     * @return boolean
     */
    public function update($composerPath, $projectName = null)
    {
        if($projectName) {
            $projectName = escapeshellarg($projectName);
        }
        
        $composerPath = escapeshellarg($composerPath);
        $status = null;
        $output = null;
        exec("cd $composerPath && composer update $projectName", $output, $status);
        
        if($status != 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * validates a composer file on given path
     * 
     * @param string $composerPath
     * @return boolean
     */
    public function validate($composerPath)
    {
        $composerPath = escapeshellarg($composerPath);
        $status = null;
        $output = null;
        
        exec("cd $composerPath && composer validate -q", $output, $status);
        
        if($status != 0) {
            return false;
        }
        
        return true;
    }
    
}
