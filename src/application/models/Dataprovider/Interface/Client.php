<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Client
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Client
{
    
    /**
     * delete client
     * 
     * @param string $clientId
     * @return boolean
     */
    public function deleteClient($clientId);
    
    /**
     * get client
     * 
     * @param string $clientId
     * @return array 
     */
    public function getClient($clientId);
    
    /**
     * get client by email
     * 
     * @param string $email
     * @return array 
     */
    public function getClientByEmail($email);
    
    /**
     * get client by label
     * 
     * @param string $label
     * @return array 
     */
    public function getClientByLabel($label);
    
    /**
     * get client by user name
     * 
     * @param string $userName
     * @return array 
     */
    public function getClientByUserName($userName);
    
    /**
     * gets user data of clients
     * 
     * @return array
     */
    public function getClients();

    /**
     * get clients by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getClientsByLastModification($limit);
    
    /**
     * get clients which have the certain service
     * 
     * @param string $serviceName
     * @return array
     */
    public function getClientsByService($serviceName);

    /**
     * paginates clients
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
     * save client
     * 
     * @param string $data
     * @param string $clientId
     * @return string id of saved client
     */
    public function saveClient($data, $clientId = null);
    
}
