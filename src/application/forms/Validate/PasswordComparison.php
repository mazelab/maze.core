<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Validate_PasswordComparison
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Validate_PasswordComparison extends Zend_Validate_Abstract
{

    const INVALID_USERID = 'invalidUserId';
    const PASSWORD_MISMATCH = 'reachedLimit';
    
    protected $_messageTemplates = array(
        self::INVALID_USERID => "UserId not given",
        self::PASSWORD_MISMATCH => 'Password ist not correct.'
    );
    
    /**
     * @var string
     */
    protected $_userId;
    
    /**
     * @param string $userId
     */
    public function __construct($userId)
    {
        $this->_userId = $userId;
    }

    public function isValid($value, $context = null)
    {
        $result = $this->validate($value);

        if (!$result){
            return false;
        }
 
        return true;
    }

    protected function validate($value)
    {
        $userId = $this->_userId;
        if(!$userId) {
            $this->_error(self::INVALID_USERID);
            return false;
        }
        $userManager = Core_Model_DiFactory::getUserManager();
        
        if(!($user = $userManager->getUser($userId))) {
            $this->_error(self::INVALID_USERID);
            return false;
        }
        
        if(!$userManager->checkAccess($user->getUsername(), $value)) {
            $this->_error(self::PASSWORD_MISMATCH);
            return false;
        }
        
        return true;
    }

}
