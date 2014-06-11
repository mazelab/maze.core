<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Node
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Node
{
    
    /**
     * deletes a certain node
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function deleteNode($nodeId);
    
    /**
     * returns a certain node
     * 
     * @param string $node
     * @return array
     */
    public function getNode($nodeId);
    
    /**
     * returns the node which has the given api key registered
     * 
     * @param string $apiKey
     * @return array
     */
    public function getNodeByApiKey($apiKey);
    
    /**
     * returns a certain node by name
     * 
     * @param string $name
     * @return array
     */
    public function getNodeByName($name);

    /**
     * returns all existing nodes
     * 
     * @return array
     */
    public function getNodes();

    /**
     * get nodes by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getNodesByLastModification($limit);
    
    /**
     * get ndoes which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getNodesByService($serviceName);

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
    public function paginate($limit, $page, $searchTerm = null);

    /**
     * updates/creates a node data
     *
     * @param array $data
     * @param string $nodeId
     * @string accountId
     * @return string|boolean mongoId or false
     */
    public function saveNode($data, $nodeId = null);
    
}
