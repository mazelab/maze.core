<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Config
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Config
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Config
{

    /**
     * collection name
     */
    CONST COLLECTION = 'config';
    
    /**
     * key name tyoe
     */
    CONST KEY_TYPE = 'type';
    
    /**
     * value name type config
     */
    CONST KEY_TYPE_CONFIG = 'config';
    
    /**
     * gets config collection
     * 
     * @return MongoCollection
     */
    protected function _getConfigCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * returns config
     * 
     * @return array
     */
    public function getConfig()
    {
        $query = array(
            self::KEY_TYPE => (string) self::KEY_TYPE_CONFIG
        );

        if(!($config = $this->_getConfigCollection()->findOne($query)) || empty($config)) {
            return array();
        }

        $config['_id'] = (string) $config['_id'];
        return $config;
    }

    /**
     * saves the given config dataset
     * 
     * @param array $data
     * @return boolean
     */
    public function saveConfig(array $data)
    {
        $query = array(
            self::KEY_TYPE => (string) self::KEY_TYPE_CONFIG
        );
        
        $newData = array(
            '$set' => $this->_getDatabase()->prepareUpdateDataSet($data)
        );
        
        $options = array(
            'upsert' => true,
            'j' => true
        );

        return $this->_getConfigCollection()->update($query, $newData, $options);
    }

}
