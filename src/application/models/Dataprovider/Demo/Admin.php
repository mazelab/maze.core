<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_Admin
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_Admin
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
    implements Core_Model_Dataprovider_Interface_Admin
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'user';

    /**
     * key name group
     */
    CONST KEY_GROUP = "group";
    
    /**
     * name of the admin group
     */
    CONST GROUP_ADMIN = "admin";
    
    /**
     * delete admin
     * 
     * @param string $adminId
     * @return boolean
     */
    public function deleteAdmin($adminId)
    {
        return false;
    }
    
    /**
     * get admin
     * 
     * @param string $adminId
     * @return array 
     */
    public function getAdmin($adminId)
    {
        return array();
    }
    
    /**
     * get admin by email
     * 
     * @param string $email
     * @return array
     */
    public function getAdminByEmail($email)
    {
        return array();
    }

    /**
     * get admin by user name
     * 
     * @param string $userName
     * @return array
     */
    public function getAdminByUserName($userName)
    {
        return array();
    }
    
    /**
     * get admins
     * 
     * @return array
     */
    public function getAdmins()
    {
        $users = array();

        foreach ($this->_getCollection(self::COLLECTION) as $user){
            if (!isset($user[self::KEY_GROUP]) || $user[self::KEY_GROUP] != self::GROUP_ADMIN){
                continue;
            }
            $users[] = $user;
        }

        return $users;
    }

    /**
     * save admin
     * 
     * @param string $data
     * @param string $adminId
     * @return string id of saved admin
     */
    public function saveAdmin($data, $adminId = null)
    {
        return false;
    }
    
}
