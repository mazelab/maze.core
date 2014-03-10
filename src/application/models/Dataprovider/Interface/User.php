<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_User
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_User
{
    
    /**
     * checks for valid user password combination
     * 
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function checkAccess($username, $password);
    
    /**
     * get user by id
     * 
     * @param string $userId
     * @return array 
     */
    public function getUser($userId);
    
    /**
     * get user by email
     * 
     * @param string $email
     * @return boolean
     */
    public function getUserByEmail($email);
    
    /**
     * get user by password token
     * 
     * @param string $token
     * @return array
     */
    public function getUserByResetPasswordToken($token);
    
    /**
     * get user by username
     * 
     * @param string $username
     * @return array 
     */
    public function getUserByUsername($username);
    
}
