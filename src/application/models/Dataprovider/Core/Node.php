<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Node
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Node 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Node
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'node';
    
    /**
     * key name api key
     */
    CONST KEY_API_KEY = 'apiKey';
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name name
     */
    CONST KEY_NAME = 'name';
    
    /**
     * key name of serivce property
     */
    CONST KEY_SERVICE = 'services';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getNodeCollection()->ensureIndex(array(
            self::KEY_API_KEY => 1,
            self::KEY_NAME => 1
        ), array('unique' => true));
    }
    
    /**
     * gets node collection
     * 
     * @return MongoCollection
     */
    protected function _getNodeCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }

    /**
     * deletes a certain node
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function deleteNode($nodeId)
    {
        $query = array(
            self::KEY_ID => new MongoId($nodeId)
        );
        
        $options = array(
            "j" => true
        );
        
        return $this->_getNodeCollection()->remove($query, $options);
    }
    
    /**
     * returns a certain node
     * 
     * @param string $id
     * @return array
     */
    public function getNode($id)
    {
        $query = array(
            self::KEY_ID => new MongoId($id)
        );

        if(!($node = $this->_getNodeCollection()->findOne($query)) || empty($node)) {
            return array();
        }
                
        $node['_id'] = (string) $node['_id'];
        return $node;
    }
    
    /**
     * returns the node which has the given api key registered
     * 
     * @param string $apiKey
     * @return array
     */
    public function getNodeByApiKey($apiKey)
    {
        $query = array(
            self::KEY_API_KEY => (string) $apiKey
        );
        
        if(!($node = $this->_getNodeCollection()->findOne($query)) || empty($node)) {
            return array();
        }
        
        $node['_id'] = (string) $node['_id'];
        return $node;
    }
    
    /**
     * returns a certain node by name
     * 
     * @param string $name
     * @return array
     */
    public function getNodeByName($name)
    {
        $query = array(
            self::KEY_NAME => (string) $name
        );
        
        if(!($node = $this->_getNodeCollection()->findOne($query)) || empty($node)) {
            return array();
        }
        
        $node['_id'] = (string) $node['_id'];
        return $node;
    }
    
    /**
     * returns all existing nodes
     * 
     * @return array
     */
    public function getNodes()
    {
        $nodes = array();
        
        foreach($this->_getNodeCollection()->find() as $nodeId => $node) {
            $node[self::KEY_ID] = $nodeId;
            $nodes[$nodeId] = $node;
        }
        
        return $nodes;
    }

    /**
     * get nodes by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getNodesByLastModification($limit)
    {
        $nodes = array();
        $sort = array(
            'modified' => -1
        );

        foreach ($this->_getNodeCollection()->find()->limit($limit)->sort($sort) as $nodeId => $node) {
            $node[self::KEY_ID] = $nodeId;
            $nodes[$nodeId] = $node;
        }

        return $nodes;
    }
    
    /**
     * get ndoes which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getNodesByService($serviceName)
    {
        $nodes = array();
        $query = array(
            self::KEY_SERVICE . '.' . (string) $serviceName => array(
                '$exists' => 1
            )
        );

        foreach($this->_getNodeCollection()->find($query) as $nodeId => $node) {
            $node[self::KEY_ID] = $nodeId;
            $nodes[$nodeId] = $node;
        }

        return $nodes;
    }

    /**
     * saves node data and returns it id
     * 
     * @param array $data
     * @return string id of saved node
     */
    public function saveNode($data, $id = null)
    {
        $mongoId = new MongoId($id);

        $data[self::KEY_ID] = $mongoId;
        
        $options = array(
            "j" => true
        );
        
        if(!$this->_getNodeCollection()->save($data, $options)) {
            return false;
        }
        
        return (string) $mongoId;
    }

}
