<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Data
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Data
{
    
    /**
     * contains builded collections
     * 
     * @var array contains MongoCollection
     */
    protected $_collections = array();
    
    /**
     * @var MongoDb_Mongo
     */
    protected $_mongoDb;

    /**
     * init mongo db
     */
    public function __construct()
    {
        $this->_mongoDb = Core_Model_DiFactory::getMongoDb();
    }
    
    /**
     * @return MongoDb_Mongo
     */
    protected function _getDatabase()
    {
        return $this->_mongoDb;
    }

    /**
     * gets collection from db
     * 
     * @param string $collectionName
     * @return MongoCollection|null
     */
    protected function _getCollection($collectionName)
    {
        if (!isset($this->_collections[$collectionName])) {
            if(!($collection = $this->_getDatabase()->getCollection($collectionName))) {
                return null;
            }
            
            $this->_collections[$collectionName] = $collection;
        }

        return $this->_collections[$collectionName];
    }

}
