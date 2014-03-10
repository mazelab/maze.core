<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Module
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Module
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Module
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'modules';

    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name installed
     */
    CONST KEY_INSTALLED = 'installed';
    
    /**
     * key name local
     */
    CONST KEY_LOCAL = 'local';
    
    /**
     * key name name
     */
    CONST KEY_NAME = 'name';
    
    /**
     * key name for remote flag
     */
    CONST KEY_REMOTE = 'remote';
    
    /**
     * key name for updateable flag
     */
    CONST KEY_UPDATEABLE = 'updateable';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getModulesCollection()->ensureIndex(array(
            self::KEY_NAME => 1
        ), array('unique' => true));
    }
    
    /**
     * gets module collection
     * 
     * @return MongoCollection
     */
    protected function _getModulesCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * deletes a certain module
     * 
     * @param string $moduleName name of the module
     * @return boolean
     */
    public function deleteModule($moduleName)
    {
        $query = array(
            self::KEY_NAME => new MongoId($moduleName)
        );

        $options = array(
            "j" => true
        );
        
        return $this->_getModulesCollection()->remove($query, $options);
    }
    
    /**
     * returns all modules without the installed flag
     * 
     * @return array
     */
    public function getAvailableModules()
    {
        $modules = array();
        $query = array(
            self::KEY_INSTALLED => array(
                '$ne' => true
            )
        );
        
        $sort = array(
            self::KEY_NAME => 1
        );
        
        foreach($this->_getModulesCollection()->find($query)->sort($sort) as $moduleName => $module) {
            $module[self::KEY_ID] = $moduleName;
            $modules[$module['name']] = $module;
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
        $query = array(
            self::KEY_NAME => (string) $moduleName
        );

        if(!($module = $this->_getModulesCollection()->findOne($query)) || empty($module)) {
            return array();
        }

        $module['_id'] = (string) $module['_id'];
        return $module;
    }

    /**
     * returns all modules which can be updated
     * 
     * @return array
     */
    public function getUpdateableModules()
    {
        $modules = array();
        $query = array(
            self::KEY_INSTALLED => true,
            self::KEY_UPDATEABLE => true
        );
        
        $sort = array(
            self::KEY_NAME => 1
        );
        
        foreach($this->_getModulesCollection()->find($query)->sort($sort) as $moduleName => $module) {
            $module[self::KEY_ID] = (string) self::KEY_ID;
            $modules[$moduleName] = $module;
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
        $query = array(
            self::KEY_NAME => $moduleName
        );
        
        if(($result = $this->_getModulesCollection()->findOne($query))) {
            $data[self::KEY_ID] = $result[self::KEY_ID];
        }
        
        $data[self::KEY_NAME] = $moduleName;
        
        $options = array(
            "j" => true
        );
        
        return $this->_getModulesCollection()->save($data, $options);
    }
    
}
