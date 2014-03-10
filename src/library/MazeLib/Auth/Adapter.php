<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Auth_Adapter
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    protected $_username;
    protected $_password;

    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    protected function _getUserData()
    {
        $userManager = Core_Model_DiFactory::getUserManager();

        return $userManager->getUserByNameAsArray($this->_username);
    }

    protected function _result($code)
    {
        return new Zend_Auth_Result($code, $this->_getUserData());
    }

    public function authenticate()
    {
        $userManager = Core_Model_DiFactory::getUserManager();

        if($userManager->checkAccess($this->_username, $this->_password)) {
            return $this->_result(Zend_Auth_Result::SUCCESS);
        } else {
            $user = $userManager->getUserByNameAsArray($this->_username);
            if($user && (!isset($user['status']) || !$user['status'])) {
                return $this->_result(MazeLib_Auth_Result::FAILURE_IDENTITY_DISABLED);
            }
        }
        
        return $this->_result(Zend_Auth_Result::FAILURE);
    }

}

