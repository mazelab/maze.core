<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_Admin
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_Admin
{
    
    /**
     * delete admin
     * 
     * @param string $adminId
     * @return boolean
     */
    public function deleteAdmin($adminId);
    
    /**
     * get admin
     * 
     * @param string $adminId
     * @return array 
     */
    public function getAdmin($adminId);
    
    /**
     * get admin by email
     * 
     * @param string $email
     * @return array
     */
    public function getAdminByEmail($email);

    /**
     * get admin by user name
     * 
     * @param string $userName
     * @return array
     */
    public function getAdminByUserName($userName);
    
    /**
     * get admins
     * 
     * @return array
     */
    public function getAdmins();

    /**
     * save admin
     * 
     * @param string $data
     * @param string $adminId
     * @return string id of saved admin
     */
    public function saveAdmin($data, $adminId = null);
    
}
