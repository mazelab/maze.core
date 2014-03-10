<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Nodes
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Nodes extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    public function init()
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $nodeOptions = array();
        
        foreach($nodeManager->getNodes() as $nodeId => $node) {
            $nodeOptions[$node->getId()] = $node->getName();
        }
        
        $this->addElement('select', 'nodes', array(
            'required' => true,
            'label' => 'Nodes',
            'class' => 'selectpicker show-menu-arrow show-tick',
            'multiOptions' => $nodeOptions
        ));

        $this->setElementDecorators($this->_elementDecorators);
    }

}

