<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_UserManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_UserManager
{

    /**
     * name of the administrators group
     */
    CONST GROUP_ADMIN = 'admin';
    
    /**
     * name of the clients group
     */
    CONST GROUP_CLIENT = 'client';
    
    /**
     * message when user used the password request
     */
    CONST MESSAGE_USER_REQUEST_PASSWORD = 'Password request for %1$s was send to %2$s';
    
    /**
     * message when user was updated
     */
    CONST MESSAGE_USER_UPDATED = 'User %1$s was updated';
    
    /**
     * message when user password was updated
     */
    CONST MESSAGE_USER_UPDATED_PASSWORD = 'Password of user %1$s was changed';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * checks access with username and passwords
     * 
     * @param string $username
     * @param string $password unencrypted
     * @return boolean
     */
    public function checkAccess($username, $password)
    {
        return $this->getProvider()->checkAccess($username, md5($password));
    }
    
    /**
     * returns a certain user object
     * 
     * @param string $clientId
     * @return Core_Model_ValueObject_Client|Core_Model_ValueObject_Admin|null
     */
    public function getUser($userId)
    {
        if(($user = Core_Model_DiFactory::getClient($userId)) ||
                ($user = Core_Model_DiFactory::getAdmin($userId)) ) {
            return $user;
        }
        
        if(!($user = $this->getProvider()->getUser($userId))) {
            return null;
        }
        
        if(!key_exists('group', $user) || !key_exists('_id', $user)) {
            return null;
        }
        
        if($user['group'] == self::GROUP_ADMIN) {
            $adminManager = Core_Model_DiFactory::getAdminManager();
            $adminManager->registerAdmin($user['_id'], $user);
            
            return $adminManager->getAdmin($user['_id']);
        } else if ($user['group'] == self::GROUP_CLIENT) {
            $clientManager = Core_Model_DiFactory::getClientManager();
            $clientManager->registerClient($user['_id'], $user);
            
            return $clientManager->getClient($user['_id']);
        }
        
        return null;
    }
    
    /**
     * gets data set of a user object
     * 
     * @param string $userId
     * @return array
     */
    public function getUserAsArray($userId)
    {
        if(!($user = $this->getUser($userId))) {
            return array();
        }
        
        return $user->getData();
    }
    
    /**
     * @return Core_Model_Dataprovider_Interface_User
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getUser();
    }
    
    /**
     * gets a certain user by email
     * 
     * @param string $userEmail
     * @return Core_Model_ValueObject_Client|Core_Model_ValueObject_Admin|null
     */
    public function getUserByEmail($userEmail)
    {
        if(($user = Core_Model_DiFactory::getClientByEmail($userEmail)) ||
                ($user = Core_Model_DiFactory::getAdminByEmail($userEmail))) {
            return $user;
        }
        
        if(!($user = $this->getProvider()->getUserByEmail($userEmail))) {
            return null;
        }
        
        if(!key_exists('group', $user) || !key_exists('_id', $user)) {
            return null;
        }
        
        if($user['group'] == self::GROUP_ADMIN) {
            $adminManager = Core_Model_DiFactory::getAdminManager();
            $adminManager->registerAdmin($user['_id'], $user);
            
            return $adminManager->getAdmin($user['_id']);
        } else if ($user['group'] == self::GROUP_CLIENT) {
            $clientManager = Core_Model_DiFactory::getClientManager();
            $clientManager->registerClient($user['_id'], $user);
            
            return $clientManager->getClient($user['_id']);
        }
        
        return null;
    }
    
    /**
     * gets a certain user by email as array
     * 
     * @param string $email
     * @return array
     */
    public function getUserByEmailAsArray($email)
    {
        if (!($user = $this->getUserByEmail($email))) {
            return array();
        }
        
        return $user->getData();
    }
    
    /**
     * gets a certain user by username
     * 
     * @param string $userName
     * @return Core_Model_ValueObject_Client|Core_Model_ValueObject_Admin|null
     */
    public function getUserByName($userName)
    {
        if(($user = Core_Model_DiFactory::getClientByUserName($userName)) ||
                ($user = Core_Model_DiFactory::getAdminByUserName($userName))) {
            return $user;
        }
        
        if(!($user = $this->getProvider()->getUserByUsername($userName))) {
            return null;
        }
        
        if(!key_exists('group', $user) || !key_exists('_id', $user)) {
            return null;
        }
        
        if($user['group'] == self::GROUP_ADMIN) {
            $adminManager = Core_Model_DiFactory::getAdminManager();
            $adminManager->registerAdmin($user['_id'], $user);
            
            return $adminManager->getAdmin($user['_id']);
        } else if ($user['group'] == self::GROUP_CLIENT) {
            $clientManager = Core_Model_DiFactory::getClientManager();
            $clientManager->registerClient($user['_id'], $user);
            
            return $clientManager->getClient($user['_id']);
        }
        
        return null;
    }
    
    /**
     * gets a certain user by username as array
     * 
     * @param string $userName
     * @return array
     */
    public function getUserByNameAsArray($userName)
    {
        if (!($user = $this->getUserByName($userName))) {
            return array();
        }
        
        return $user->getData();
    }
    
    /**
     * returns id of a certain user if token valid
     * 
     * @param string $token
     * @return string user id
     */
    public function getUserByResetPasswordTocken($token)
    {
        if (!($user = $this->getProvider()->getUserByResetPasswordToken($token)) 
                || !key_exists('_id', $user)) {
            return "";
        }
        
        return $user['_id'];
    }
    
    /**
     * reset the user password
     * 
     * @param string $userId
     * @param array $data
     * @return boolean
     */
    public function resetPassword($userId, $data)
    {
        if (!key_exists("password", $data) || !($user = $this->getUser($userId))){
            return false;
        }

        $user->setData(array('password' => $data["password"]));
        $user->unsetProperty('resetPassword');
        if (!$user->save()){
            return false;
        }
        
        if($user instanceof Core_Model_ValueObject_Admin) {
            $this->_getLogger()->setMessage(Core_Model_AdminManager::MESSAGE_ADMIN_UPDATED_PASSWORD);
        } else if($user instanceof Core_Model_ValueObject_Client) {
            $this->_getLogger()->setMessage(Core_Model_ClientManager::MESSAGE_CLIENT_UPDATED_PASSWORD)
                 ->setClientRef($user->getId());
        } else {
            $this->_getLogger()->setMessage(self::MESSAGE_USER_UPDATED_PASSWORD);
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessageVars($user->getLabel())->save();
        
        return true;
    }
    
    /**
     * set the reset password flag of the given user
     *
     * @param string $userEmail
     * @return string $token|null
     */
    public function setResetPasswordFlag($userEmail)
    {
        if (!($user = $this->getUserByEmail($userEmail))){
            return null;
        }

        $token = sha1($userEmail . time());
        $resetPassword = array(
            "resetPassword" => array(
                "token" => $token,
                "date"  => Zend_Date::now()->get(Zend_Date::ISO_8601)
            )
        );

        if($user instanceof Core_Model_ValueObject_Client) {
            $this->_getLogger()->setClientRef($user->getId());
        }
        
        if ($user->setData($resetPassword)->save()){
            $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                    ->setMessage(self::MESSAGE_USER_REQUEST_PASSWORD)
                    ->setMessageVars($user->getLabel(), $userEmail)->save();
            
            return $token;
        }
        
        return null;
    }
    
    /**
     * update user password
     * 
     * @todo old password valdation in form
     * 
     * @param string $userId
     * @param array $data
     * @return boolean
     */
    public function updateUserPassword($userId, $data)
    {
        if(!($user = $this->getUser($userId))) {
            return false;
        }

        if(key_exists('newPassword', $data)) {
            if($user->setData(array('password' => $data['newPassword']))->save()) {
                if($user instanceof Core_Model_ValueObject_Admin) {
                    $this->_getLogger()->setMessage(Core_Model_AdminManager::MESSAGE_ADMIN_UPDATED_PASSWORD);
                } else if($user instanceof Core_Model_ValueObject_Client) {
                    $this->_getLogger()->setMessage(Core_Model_ClientManager::MESSAGE_CLIENT_UPDATED_PASSWORD)
                            ->setClientRef($user->getId());
                } else {
                    $this->_getLogger()->setMessage(self::MESSAGE_USER_UPDATED_PASSWORD);
                }
                
                $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                        ->setMessageVars($user->getLabel())->save();
                
                return true;
            }
        }
        
        return false;
    }
    
}

