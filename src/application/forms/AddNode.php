<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AddNode
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AddNode extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    public function __construct($options = null)
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        parent::__construct($options);
    }
    
    public function init()
    {
        $this->addElement("select", "nodetype", array(
            "label" => "node type",
            "multiOptions" => array(
                "" => "Assign a server type",
                Core_Form_Node::SERVER_VIRTUAL   => "Virtual Server",
                Core_Form_Node::SERVER_CLOUD     => "Cloud Server‎",
                Core_Form_Node::SERVER_DEDICATED => "Dedicated Server‎"
            ),
            "class" => "selectpicker show-menu-arrow show-tick",
            "value" => array("")
        ));
        
        $this->addElement('text', 'name', array(
            'required' => true,
            'label' => 'node name *',
        ));
        $this->addElement('text', 'ipAddress', array(
            'label' => 'ip address *',
            'required' => true,
            'validators' => array(
                new Zend_Validate_Hostname(
                    array(
                        'allow' => Zend_Validate_Hostname::ALLOW_DNS
                                 | Zend_Validate_Hostname::ALLOW_IP,
                        'idn'   => true
                    )
                )
            )
        ));
        $this->addElement('hidden', 'apiKey');

        $this->setElementDecorators($this->_elementDecorators);
    }

}

