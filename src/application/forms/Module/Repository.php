<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Module_Repository
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Module_Repository extends Zend_Form
{
    protected $_repositoryTypes = array(
        'vcs' => 'vcs'
    );
    
    public function init()
    {
        $this->addElement('text', 'name', array(
            'required' => true,
        ));
        $this->addElement('text', 'version', array(
            'required' => true,
            'validators' => array(
                array("Regex", false, '/^(\d+\.\d+(\.\d+)*)$/')
            )
        ));
        $this->addElement('text', 'url', array(
            'required' => true,
        ));
        $this->addElement('select', 'type', array(
            'required' => true,
            'multiOptions' => $this->_repositoryTypes
        ));
    }
    
}

