<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Service_DiFactory
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Service_DiFactory
{
    
    /**
     * @var Core_Service_Interface_Composer
     */
    static protected $_composer;

    /**
     * gets composer instance
     * 
     * @return Core_Service_Interface_Composer
     */
    static public function getComposer()
    {
        if (!self::$_composer instanceof Core_Service_Interface_Composer) {
            self::$_composer = self::newComposer();
        }

        return self::$_composer;
    }
    
    /**
     * sets actual composer instance 
     * 
     * @param Core_Service_Interface_Composer $composer
     */
    static public function setComposer(Core_Service_Interface_Composer $composer)
    {
        self::$_composer = $composer;
    }
    
    /**
     * create new composer instance
     * 
     * @return Core_Service_Interface_Composer
     */
    static public function newComposer()
    {
        return new Core_Service_Composer();
    }
    
}
