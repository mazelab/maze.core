<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_User
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_User 
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase 
    implements Core_Model_Dataprovider_Interface_User
{

    /**
     * collection name
     */
    CONST COLLECTION = 'user';
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name password
     */
    CONST KEY_PASSWORD = 'password';
    
    /**
     * key name username
     */
    CONST KEY_USERNAME = 'username';
    
    /**
     * checks for valid user password combination
     * 
     * @param string $username
     * @param string $password
     * @return boolean 
     */
    public function checkAccess($username, $password)
    {
        $user = $this->getUserByUsername($username);
        if (!array_key_exists('status', $user) || $user['status'] == false) {
            return false;
        }
        
        if ($user && array_key_exists(self::KEY_PASSWORD, $user) && $password == $user[self::KEY_PASSWORD]) {
            return true;
        }

        return false;
    }
    
    /**
     * get user by id
     * 
     * @param string $userId
     * @return array 
     */
    public function getUser($userId)
    {
        $userCollection = $this->_getCollection(self::COLLECTION);

        foreach ($userCollection as $key => $value) {
            if ($key == $userId) {
                return $value;
            }
        }

        return null;
    }

    /**
     * get user by email
     * 
     * @param string $email
     * @return boolean
     */
    public function getUserByEmail($email)
    {
        return false;
    }

    /**
     * get user by password token
     * 
     * @param string $token
     * @return array
     */
    public function getUserByResetPasswordToken($token)
    {
        return false;
    }
    
    /**
     * get user by username
     * 
     * @param string $username
     * @return array 
     */
    public function getUserByUsername($username)
    {
        $userCollection = $this->_getCollection(self::COLLECTION);

        foreach ($userCollection as $key => $value) {
            if ($value[self::KEY_USERNAME] == $username) {
                return $value;
            }
        }

        return null;
    }
    
}
