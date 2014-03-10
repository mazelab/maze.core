<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AddAdmin
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AddAdmin extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    public function init()
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        $this->addElement('text', 'email', array(
            'label' => 'E-mail address *',
            'required' => true,
            'validators' => array(
                array('EmailAddress'),
                new Core_Form_Validate_UniqueEmail
            )
        ));
        $this->addElement('text', 'username', array(
            'label' => 'username *',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(3)),
                new Core_Form_Validate_ExistsUsername
            )
        ));
        $this->addElement('password', 'password', array(
            'label' => 'password *',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ))->setElementDecorators($this->_elementDecorators);
    }
}

