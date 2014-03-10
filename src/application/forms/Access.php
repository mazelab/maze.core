<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Access
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Access extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    public function init()
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        
        $this->addElement('text', 'username', array(
            'jsLabel' => 'username',
            'label' => 'username',
            'validators' => array(
                array('StringLength', NULL, array(4))
            ),
            'helper' => 'formTextAsSpan'
        ));
        
        $this->addElement('password', 'newPassword', array(
            'label' => 'new password',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ));

        $this->addElement('password', 'confirmPassword', array(
            'label' => 'confirm password',
            'required' => true,
            'ignore' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                array('identical', true, array('newPassword'))
            )
        ));
        $this->setElementDecorators($this->_elementDecorators);
    }
    
    /**
     * adds validator for comparison with old password
     * 
     * @param string $userId
     * @return Core_Form_Access
     */
    public function addPasswordComparison($userId)
    {
        $this->addElement('password', 'oldPassword', array(
            'label' => 'old password',
            'required' => true,
            'validators' => array(
                new Core_Form_Validate_PasswordComparison($userId)
            )
        ));
        
        return $this;
    }

}

