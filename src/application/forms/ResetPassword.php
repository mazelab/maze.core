<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_ResetPassword
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_ResetPassword extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->addElement('text', 'userEmail', array(
            'required'   => true,
            'label' => 'E-mail address:',
            'validators' => array(
                array('EmailAddress')
            )
        ));
        
        $this->addElement('password', 'password', array(
            'jsLabel' => 'password',
            'label' => 'password',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                array('identical', true, array('confirmPassword'))
            )
        ));

        $this->addElement('password', 'confirmPassword', array(
            'label' => 'confirm password',
            'ignore' => true,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', NULL, array(4)),
                array('identical', true, array('password'))
            )
        ));
    }
}