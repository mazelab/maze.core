<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Adminuser
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Adminuser extends Zend_Form
{

    public function init()
    {
        $this->addElement("text", "email", array(
            "required" => "true",
            "label" => "E-mail address:",
            "validators" => array(
                array("EmailAddress")
            )
        ));
        $this->addElement("text", "username", array(
            "required" => "true",
            "label" => "username",
            "validators" => array(
                array("StringLength", NULL, array(4))
            )
        ));
        $this->addElement("password", "password", array(
            "required" => "true",
            "label" => "password",
            "validators" => array(
                array("StringLength", NULL, array(4))
            )
        ));
        $this->addElement("password", "passwordRepeat", array(
            "required" => "true",
            "label" => "confirm password",
            "validators" => array(
                array('identical', true, array('password')),
                array("StringLength", NULL, array(4))
            )
        ));
        $this->addElement('text', 'company', array(
            "label" => "company"
        ));
    }

}

