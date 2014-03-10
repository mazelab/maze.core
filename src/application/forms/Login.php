<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Login
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->addElement('text', 'username', array(
            'required' => 'true',
            'label' => 'username',
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ));
        $this->addElement('password', 'password', array(
            'required' => 'true',
            'label' => 'password',
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ));
    }


}

