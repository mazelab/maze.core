<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_AdminManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_AdminManager
{

    /**
     * name of the administrators group
     */
    CONST GROUP_ADMIN = Core_Model_UserManager::GROUP_ADMIN;
    
    /**
     * message when admin was activated
     */
    CONST MESSAGE_ADMIN_ACTIVATED = 'Admin %1$s was activated';
    
    /**
     * message when admin was created
     */
    CONST MESSAGE_ADMIN_CREATED = 'Admin %1$s was created';
    
    /**
     * message when admin was deactivated
     */
    CONST MESSAGE_ADMIN_DEACTIVATED = 'Admin %1$s was deactivated';
    
    /**
     * message when admin was deleted
     */
    CONST MESSAGE_ADMIN_DELETED = 'Admin %1$s was deleted';
    
    /**
     * message when admin was updated
     */
    CONST MESSAGE_ADMIN_UPDATED = 'Admin %1$s was updated';
    
    /**
     * message when admin password was updated
     */
    CONST MESSAGE_ADMIN_UPDATED_PASSWORD = 'Password of admin %1$s was changed';

    /**
     * 
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain admin instance if registered
     * 
     * @param string $adminId
     * @return Vpopmail_Model_ValueObject_Client|null null if not registered
     */
    protected function _getRegisteredAdmin($adminId)
    {
        if(!$this->isAdminRegistered($adminId)) {
            return null;
        }
        
        return Core_Model_DiFactory::getAdmin($adminId);
    }
    
    /**
     * loads and registers a certain admin instance
     * 
     * @param string $adminId
     * @return boolean
     */
    protected function _loadAdmin($adminId)
    {
        if(!$adminId) {
            return null;
        }
        
        $data = $this->getProvider()->getAdmin($adminId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerAdmin($adminId, $data);
    }
    
    /**
     * loads and registers a certain admin instance
     * 
     * @param string $email
     * @return boolean
     */
    protected function _loadAdminByEmail($email)
    {
        if(!$email) {
            return null;
        }
        
        $data = $this->getProvider()->getAdminByEmail($email);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerAdmin($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * loads and registers a certain admin instance
     * 
     * @param string $userName
     * @return boolean
     */
    protected function _loadAdminByUserName($userName)
    {
        if(!$userName) {
            return null;
        }
        
        $data = $this->getProvider()->getAdminByUserName($userName);
        if(empty($data) || !array_key_exists('_id', $data)) {
            return null;
        }
        
        if(!$this->registerAdmin($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * changes the status of the given admin
     * 
     * @param string $adminId
     * @return boolean
     */
    public function changeAdminState($adminId)
    {
        if(!($admin = $this->getAdmin($adminId))) {
            return false;
        }
        
        $currentStatus = $admin->getStatus();
        if(!$currentStatus || $currentStatus === false) {
            if(!$admin->activate()) {
                return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_ADMIN_ACTIVATED);
        } else {
            if(!$admin->deactivate()) {
                return false;
            }
            
            $this->_getLogger()->setMessage(self::MESSAGE_ADMIN_DEACTIVATED);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessageVars($admin->getLabel())->save();
        
        return true;
    }
    
    /**
     * creates a new admin with the given data
     * 
     * @param array $data
     * @return string|null admin id
     */
    public function createAdmin($data)
    {
        if(!isset($data['username'])) {
            return false;
        }

        $admin = Core_Model_DiFactory::newAdmin();
        $data['group'] = self::GROUP_ADMIN;
        $data["status"] = isset($data["status"]) ? (boolean) $data["status"] : true;
        
        if (!$admin->setData($data)->save()) {
            return false;
        }
        
        $this->registerAdmin($admin->getId(), $admin);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
            ->setMessage(self::MESSAGE_ADMIN_CREATED)->setMessageVars($admin->getLabel())
            ->setData($admin->getData())->save();
        
        return $admin->getId();
    }
    
    /**
     * deletes a certain admin
     * 
     * @param string $adminId
     * @return boolean 
     */
    public function deleteAdmin($adminId)
    {
        if(!($admin = $this->getAdmin($adminId))) {
            return false;
        }

        if(!$this->getProvider()->deleteAdmin($this->getId())) {
            return false;
        }

        Core_Model_DiFactory::getIndexManager()->unsetClient($adminId);
        $this->unregisterAdmin($adminId);
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_ADMIN_DELETED)->setMessageVars($admin->getLabel())
                ->save();
        
        return true;
    }
    
    /**
     * returns a certain admin object
     * 
     * @param string $adminId
     * @return Core_Model_ValueObject_Admin
     */
    public function getAdmin($adminId)
    {
        if(!$this->isAdminRegistered($adminId)) {
            $this->_loadAdmin($adminId);
        }
        
        return $this->_getRegisteredAdmin($adminId);
    }
    
    /**
     * gets data set of a certain admin
     * 
     * @param string $adminId
     * @return array
     */
    public function getAdminAsArray($adminId)
    {
        if(!($admin = $this->getAdmin($adminId))) {
            return array();
        }
        
        return $admin->getData();
    }

    /**
     * return admin instance by email
     * 
     * @param string $email
     * @return Core_Model_ValueObject_Admin|null
     */
    public function getAdminByEmail($email)
    {
        if(($admin = Core_Model_DiFactory::getAdminByEmail($email))) {
            return $admin;
        }
        
        if(!($adminId = $this->_loadAdminByEmail($email))) {
            return null;
        }
        
        return $this->_getRegisteredAdmin($adminId);
    }
    
    /**
     * return admin instance by user name
     * 
     * @param string $userName
     * @return Core_Model_ValueObject_Admin|null
     */
    public function getAdminByUserName($userName)
    {
        if(($admin = Core_Model_DiFactory::getAdminByUserName($userName))) {
            return $admin;
        }
        
        if(!($adminId = $this->_loadAdminByUserName($userName))) {
            return null;
        }
        
        return $this->_getRegisteredAdmin($adminId);
    }
    
    /**
     * gets all existing admins
     * 
     * @return array contains Core_Model_ValueObject_Admin
     */
    public function getAdmins()
    {
        $admins = array();
        
        foreach($this->getProvider()->getAdmins() as $adminId => $admin) {
            $this->registerAdmin($adminId, $admin);
            $admins[$adminId] = $this->_getRegisteredAdmin($adminId);
        }
        
        return $admins;
    }
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Admin
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getAdmin();
    }
    
    /**
     * checks if a certain admin instance is allready registered
     * 
     * @param string $adminId
     * @return boolean
     */
    public function isAdminRegistered($adminId)
    {
        if(Core_Model_DiFactory::isAdminRegistered($adminId)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * registers a admin instance
     * 
     * overwrites existing instances
     * 
     * @param string $adminId
     * @param mixed $context array or Vpopmail_Model_ValueObject_Admin
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerAdmin($adminId, $context, $setLoadedFlag = true)
    {
        $admin = null;
        
        if(is_array($context)) {
            $admin = Core_Model_DiFactory::newAdmin($adminId);
            
            if($setLoadedFlag) {
                $admin->setLoaded(true);
            }
            
            $admin->getBean()->setBean($context);
        } elseif($context instanceof Core_Model_ValueObject_Admin) {
            $admin = $context;
        }
        
        if(!$admin || !$admin instanceof Core_Model_ValueObject_Interface_User) {
            return false;
        }
        
        Core_Model_DiFactory::registerAdmin($adminId, $admin);
        
        return true;
    }
    
    /**
     * unregisters a certain admin instance
     * 
     * @param string $adminId
     * @return boolean
     */
    public function unregisterAdmin($adminId)
    {
        if(!$this->_getRegisteredAdmin($adminId)) {
            return true;
        }
        
        Core_Model_DiFactory::unregisterAdmin($adminId);
    }
    
    /**
     * updates a certain admin
     * 
     * @param string $adminId
     * @param array $data
     * @return boolean
     */
    public function updateAdmin($adminId, array $data)
    {
        if(!($admin = $this->getAdmin($adminId))) {
            return false;
        }

        if(!$admin->setData($data)->save()) {
            return false;
        }
        
        if(isset($data['password'])) {
            $this->_getLogger()->setMessage(self::MESSAGE_ADMIN_UPDATED_PASSWORD);
        } else {
            $this->_getLogger()->setMessage(self::MESSAGE_ADMIN_UPDATED)->setData($data);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessageVars($admin->getLabel())->save();
        
        return true;
    }
    
}

