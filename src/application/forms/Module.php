<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Module
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Module extends Zend_Form
{
    
    public function init()
    {
        $this->addElement('text', 'name', array(
            'required' => true,
            'validators' => array(
                array("Regex", false, '/^[\w-]*$/')
            )
        ));
        $this->addElement('text', 'label', array(
            'required' => true
        ));
        $this->addElement('text', 'vendor', array(
            'required' => true
        ));
        $this->addElement('text', 'description', array(
            'required' => true,
        ));
    }
    
    /**
     * sets the module repository subform from Core_Form_Module_Repsitory
     * 
     * @return Core_Form_Module
     */
    public function setRepositorySubForm()
    {
        $this->addSubForm(new Core_Form_Module_Repository, 'repository');
        
        return $this;
    }
    
}

