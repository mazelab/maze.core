<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Domain
{
    
    /**
     * deletes a certain domain
     * 
     * @param string $domainId
     * @return boolean
     */
    public function deleteDomain($domainId);
    
    /**
     * returns a certain domain
     * 
     * @param string $domainId
     * @return array
     */
    public function getDomain($domainId);
    
    /**
     * returns a certain domain by domain name
     * 
     * @param string $domainName
     * @return array
     */
    public function getDomainByName($domainName);
    
    /**
     * returns all existing domains
     * 
     * @return array
     */
    public function getDomains();

    /**
     * get domains by the last modification time
     * 
     * @param  integer $limit
     * @return array
     */
    public function getDomainsByLastModification($limit);
    
    /**
     * returns all domains by the client id (owner)
     * 
     * @param string $clientId
     * @return array
     */
    public function getDomainsByOwner($clientId);

    /**
     * get domains which have the certain service
     * 
     * @param string $serviceName
     * @param string $clientId only domains of this client
     * @return array
     */
    public function getDomainsByService($serviceName, $clientId = null);

    /**
     * paginates domains
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
     * updates/create domain with the given data set
     * 
     * @param array $data
     * @param string $domainId
     * @return boolean
     */
    public function saveDomain($data, $domainId);

}
