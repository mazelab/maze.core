<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Admin
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Admin
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Admin
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
     * value group type client
     */
    CONST KEY_GROUP_ADMIN = Core_Model_UserManager::GROUP_ADMIN;
    
    /**
     * key name id
     */
    CONST KEY_ID = '_id';
    
    /**
     * key name user name
     */
    CONST KEY_USERNAME = 'username';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_EMAIL => 1,
            self::KEY_GROUP => 1
        ));
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_GROUP => 1
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
     * delete admin
     * 
     * @param string $adminId
     * @return boolean
     */
    public function deleteAdmin($adminId)
    {
        $query = array(
            self::KEY_ID => new MongoId($adminId),
            self::KEY_GROUP => self::KEY_GROUP_ADMIN
        );
        
        $options = array(
            'j' => true
        );
        
        return $this->_getUserCollection()->remove($query, $options);
    }
    
    /**
     * get admin
     * 
     * @param string $adminId
     * @return array 
     */
    public function getAdmin($adminId)
    {
        $query = array(
            self::KEY_ID => new MongoId($adminId),
            self::KEY_GROUP => self::KEY_GROUP_ADMIN
        );
        
        if(!($admin = $this->_getUserCollection()->findOne($query)) || empty($admin)) {
            return array();
        }
        
        $admin[self::KEY_ID] = (string) $admin[self::KEY_ID];
        return $admin;
    }
    
    /**
     * get admin by email
     * 
     * @param string $email
     * @return array
     */
    public function getAdminByEmail($email)
    {
        $query = array(
            self::KEY_EMAIL => $email,
            self::KEY_GROUP => self::KEY_GROUP_ADMIN
        );
        
        if(!($admin = $this->_getUserCollection()->findOne($query)) || empty($admin)) {
            return array();
        }
        
        $admin[self::KEY_ID] = (string) $admin[self::KEY_ID];
        return $admin;
    }

    /**
     * get admin by user name
     * 
     * @param string $userName
     * @return array
     */
    public function getAdminByUserName($userName)
    {
        $query = array(
            self::KEY_USERNAME => $userName,
            self::KEY_GROUP => self::KEY_GROUP_ADMIN
        );
        
        if(!($admin = $this->_getUserCollection()->findOne($query)) || empty($admin)) {
            return array();
        }
        
        $admin[self::KEY_ID] = (string) $admin[self::KEY_ID];
        return $admin;
    }
    
    /**
     * get admins
     * 
     * @return array
     */
    public function getAdmins()
    {
        $admins = array();
        $query = array(
            self::KEY_GROUP => self::KEY_GROUP_ADMIN
        );
        
        foreach($this->_getUserCollection()->find($query) as $adminId => $admin) {
            $admin[self::KEY_ID] = $adminId;
            $admins[$adminId] = $admin;
        }
        
        return $admins;
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
        $mongoId = new MongoId($adminId);

        $data[self::KEY_ID] = $mongoId;
        
        $options = array(
            "j" => true
        );
        
        if(!$this->_getUserCollection()->save($data, $options)) {
            return false;
        }
        
        return (string) $mongoId;
    }
    
}
