<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Module
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Module 
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase 
    implements Core_Model_Dataprovider_Interface_Module
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'modules';
    
    /**
     * deletes given module in data backend
     * 
     * @param string $moduleName name of the module
     * @return boolean
     */
    public function deleteModule($moduleName)
    {
        $backend = $this->_getCollection(self::COLLECTION);
        $found = false;
        
        foreach($backend as $key => $module) {
            if(key_exists('name', $module) && $module['name'] == $moduleName) {
                $found = true;
                break;
            }
        }
        
        if(!$found) {
            return false;
        }

        unset($backend[$key]);
        
        $this->_setCollection(self::COLLECTION, $backend);

        return true;
    }
    
    /**
     * returns all modules without the installed flag
     * 
     * @return array
     */
    public function getAvailableModules()
    {
        $modules = array();
        
        foreach($this->_getCollection(self::COLLECTION) as $moduleName => $module) {
            if(!key_exists('installed', $module) || $module['installed'] !== true) {
                $modules[$moduleName] = $module;
            }
        }
        
        return $modules;
    }
    
    /**
     * returns module data
     * 
     * @param string $moduleName name of the module
     * @return array
     */
    public function getModule($moduleName)
    {
        $backend = $this->_getCollection(self::COLLECTION);
        
        foreach($backend as $module) {
            if(key_exists('name', $module) && $module['name'] == $moduleName) {
                return $module;
            }
        }
        
        return array();
    }

    /**
     * returns all modules which can be updated
     * 
     * @return array
     */
    public function getUpdateableModules()
    {
        $modules = array();
        
        foreach($this->_getCollection(self::COLLECTION) as $moduleName => $module) {
            if(key_exists('installed', $module) && $module['installed'] === true
                    && key_exists('updateable', $module) && $module['updateable'] === true) {
                $modules[$moduleName] = $module;
            }
        }

        return $modules;
    }

    /**
     * saves the given module dataset
     * 
     * @param string $moduleName name of the module
     * @param array $data
     * @return boolean
     */
    public function saveModule($moduleName, $data)
    {
        $backend = $this->_getCollection(self::COLLECTION);
        $seted = false;
        
        if(!is_array($data) || empty($data)) {
            return false;
        }
        
        foreach ($backend as $module) {
            if(key_exists('name', $module) && $module['name'] == $moduleName) {
                $data = array_merge_recursive($module, $data);
                
                $seted = true;
            }
        }

        if(!$seted) {
            $data['_id'] = $this->_generateId();
            $data['name'] = $moduleName;
        }
        
        $backend[$data['_id']] = $data;
        
        $this->_setCollection(self::COLLECTION, $backend);
        
        return true;
    }
    
}
