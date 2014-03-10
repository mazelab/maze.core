<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Validate_UniqueEmail
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Validate_UniqueEmail extends Zend_Validate_Abstract
{
    CONST EMAIL_EXISTS = "emailAlreadyInUse";

    protected $_messageTemplates = array(
        self::EMAIL_EXISTS => "email '%value%' is already in use"
    );

    public function isValid($value)
    {
        $result = $this->validate($value);

        if (!$result){
            return false;
        }
 
        return true;
    }

    protected function validate($email)
    {
        $userManager = Core_Model_DiFactory::getUserManager();
        
        if ($userManager->getUserByEmail($email)){
            $this->_error(self::EMAIL_EXISTS, $email);
            return false;
        }

        return true;
    }


}
