<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Node
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Node
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase 
    implements Core_Model_Dataprovider_Interface_Node
{

    CONST COLLECTION = 'node';
    
    /**
     * deletes a certain node
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function deleteNode($nodeId)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if (!isset($collection[$nodeId])) {
            return false;
        }

        unset($collection[$nodeId]);
        $this->_setCollection(self::COLLECTION, $collection);

        return true;
    }

    /**
     * returns a certain node
     * 
     * @param string $node
     * @return array
     */
    public function getNode($id)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($index == $id) {
                return $value;
            }
        }
        
        return array();
    }
    
    /**
     * returns the node which has the given api key registered
     * 
     * @param string $apiKey
     * @return array
     */
    public function getNodeByApiKey($apiKey)
    {
        return array();
    }
    
    /**
     * returns a certain node by name
     * 
     * @param string $name
     * @return array
     */
    public function getNodeByName($name)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        foreach ($collection as $index => $value) {
            if ($value['name'] == $name) {
                return $value;
            }
        }

        return false;
    }

    /**
     * returns all existing nodes
     * 
     * @return array
     */
    public function getNodes()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * get nodes by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getNodesByLastModification($limit)
    {
        return array();
    }
    
    /**
     * get ndoes which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getNodesByService($serviceName)
    {
        return array();
    }

    /**
     * paginates nodes
     *
     * example return:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     *
     * @param int $limit
     * @param int $page
     * @param string $searchTerm
     * @return array
     */
    public function paginate($limit, $page, $searchTerm = null)
    {
        return array(
            'total' => 0,
            'data' => array()
        );
    }

    /**
     * updates/creates a node data
     *
     * @param array $data
     * @param string $nodeId
     * @return string|boolean mongoId or false
     */
    public function saveNode($data, $nodeId = null)
    {
        $collection = $this->_getCollection(self::COLLECTION);

        if (!array_key_exists($nodeId, $collection)) {
            $data['_id'] = $nodeId;
            $collection[$nodeId] = array();
        }

        $collection[$nodeId] = array_merge($collection[$nodeId], $data);
        $this->_setCollection(self::COLLECTION, $collection);

        return $nodeId;
    }

}
