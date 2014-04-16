<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_User
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_User 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_User
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'user';
    
    /**
     * key name email
     */
    CONST KEY_EMAIL = 'email';
    
    /**
     * key name group
     */
    CONST KEY_GROUP = 'group';
    
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
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_EMAIL => 1
        ));
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_USERNAME => 1
        ), array('unique' => true));
    }
    
    /**
     * gets user collection
     * 
     * @return MongoCollection
     */
    protected function _getUserCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * checks for valid user password combination
     * 
     * @param string $username
     * @param string $password
     * @return boolean 
     */
    public function checkAccess($username, $password)
    {
        if(!($user = Core_Model_DiFactory::getUserManager()->getUserByNameAsArray($username))) {
            return false;
        }
        
        if(!array_key_exists('status', $user) || $user['status'] == false) {
            return false;
        }
        
        if (array_key_exists(self::KEY_PASSWORD, $user) && $password == $user[self::KEY_PASSWORD]) {
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
        $query = array(
            self::KEY_ID => new MongoId($userId)
        );
        
        if(!($user = $this->_getUserCollection()->findOne($query)) || empty($user)) {
            return array();
        }
        
        $user[self::KEY_ID] = (string) $user[self::KEY_ID];
        return $user;
    }
    
    /**
     * get user by email
     * 
     * @param string $email
     * @return boolean
     */
    public function getUserByEmail($email)
    {
        $query = array(
           self::KEY_EMAIL  => (string) $email
        );
        
        if(!($user = $this->_getUserCollection()->findOne($query)) || empty($user)) {
            return false;
        }

        $user[self::KEY_ID] = (string) $user[self::KEY_ID];
        return $user;
    }

    /**
     * get user by password token
     * 
     * @param string $token
     * @return array
     */
    public function getUserByResetPasswordToken($token)
    {
        $query = array(
            "resetPassword.token" => $token,
            "resetPassword.date" => array(
                '$gt'  => Zend_Date::now()->addHour(-24)->get(Zend_Date::ISO_8601),
                '$lte' => Zend_Date::now()->get(Zend_Date::ISO_8601)
            )
        );
        
        if(!($user = $this->_getUserCollection()->findOne($query)) || empty($user)) {
            return array();
        }
            
        $user[self::KEY_ID] = (string) $user[self::KEY_ID];
        return $user;
    }
    
    /**
     * get user by username
     * 
     * @param string $username
     * @return array 
     */
    public function getUserByUsername($username)
    {
        $query = array(
            self::KEY_USERNAME => (string) $username
        );
        
        if(!($user = $this->_getUserCollection()->findOne($query)) || empty($user)) {
            return array();
        }
            
        $user[self::KEY_ID] = (string) $user[self::KEY_ID];
        return $user;
    }

}
