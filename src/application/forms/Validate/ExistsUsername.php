<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Validate_ExistsUsername
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Validate_ExistsUsername extends Zend_Validate_Abstract
{
    CONST NAME_EXISTS = "nameAlreadyInUse";

    protected $_messageTemplates = array(
        self::NAME_EXISTS => "username '%value%' is already in use"
    );

    public function isValid($username)
    {
        $result = $this->validate($username);

        if (!$result){
            return false;
        }
 
        return true;
    }

    protected function validate($username)
    {
        $userManager = Core_Model_DiFactory::getUserManager();
        
        $this->_setValue($username);

        if ($userManager->getUserByName($username)){
            $this->_error(self::NAME_EXISTS);
            return false;
        }

        return true;
    }


}
