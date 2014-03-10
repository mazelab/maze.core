<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Reconfigure
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Reconfigure extends Zend_Form
{

    public function init()
    {
        $formDatabase  = new Core_Form_Database;
        $forLanguages  = new Core_Form_Languages;
        $formAdminuser = new Core_Form_Adminuser;

        $this->addElements($formDatabase->getElements())
             ->addElements($forLanguages->getElements())
             ->addElements($formAdminuser->getElements());
    }
}