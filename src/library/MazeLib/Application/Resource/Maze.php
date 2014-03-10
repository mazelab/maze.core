<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Application_Resource_Maze
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Application_Resource_Maze
    extends Zend_Application_Resource_ResourceAbstract
{
    
    /**
     * Subdirectory within a maze module containing controllers; defaults to 'controllers'
     * @var string
     */
    protected $_moduleControllerDirectoryName = 'controllers';
    
    public function _addModuleDirectory($path)
    {
        try{
            $dir = new DirectoryIterator($path);
        } catch(Exception $e) {
            throw new MazeLib_Application_Resource_Exception("Maze module directory $path not readable", 0, $e);
        }
        
        foreach ($dir as $vendor) {
            if ($vendor->isDot() || !$vendor->isDir()) {
                continue;
            }

            $this->_addModuleVendorDirectory($vendor->getFilename(), $vendor->getPathname());
        }
    }
    
    public function _addModuleVendorDirectory($vendor, $path)
    {
        try{
            $dir = new DirectoryIterator($path);
        } catch(Exception $e) {
            throw new MazeLib_Application_Resource_Exception("Maze module vendor directory $path not readable", 0, $e);
        }
        
        $front = $this->_getFrontController();
        $vendor = strtolower($vendor);
        
        foreach($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $module = $vendor.'-'.$file->getFilename();

            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
                continue;
            }

            $moduleDir = $file->getPathname() . DIRECTORY_SEPARATOR . $this->getModuleControllerDirectoryName();
            $front->addControllerDirectory($moduleDir, $module);
        }
    }
    
    protected function _getFrontController()
    {
        return Zend_Controller_Front::getInstance();
    }
    
    /**
     * Return the directory name within a maze module containing controllers
     *
     * @return string
     */
    public function getModuleControllerDirectoryName()
    {
        return $this->_moduleControllerDirectoryName;
    }
    
    /**
     * initialize maze modules
     */
    public function init()
    {
        foreach($this->getOptions() as $key => $value) {
            switch (strtolower($key)) {
                case 'moduledirectory':
                    if (is_string($value) && is_dir($value)) {
                        $this->_addModuleDirectory($value);
                    }
                    break;
                case 'modulecontrollerdirectoryname':
                    $this->setModuleControllerDirectoryName($value);
                    break;
            }
        }
    }
    
    /**
     * Set the directory name within a maze module containing controllers
     *
     * @param  string $name
     */
    public function setModuleControllerDirectoryName($name = 'controllers')
    {
        $this->_moduleControllerDirectoryName = (string) $name;
    }
    
}