<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Node
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Node extends Core_Form_AdditionalInfo
{
    /**
     * types of node servers
     */
    const SERVER_DEDICATED = "dedicated";
    const SERVER_VIRTUAL   = "virtual";
    const SERVER_CLOUD     = "cloud";

    protected $_elementDecorators = array(
        'ViewHelper'
    );
    
    public function init()
    {
        $this->addElement("select", "nodetype", array(
            "label" => "node type",
            "multiOptions" => array(
                "" => "Assign a server type",
                self::SERVER_VIRTUAL => "Virtual Server",
                self::SERVER_CLOUD => "Cloud Server‎",
                self::SERVER_DEDICATED => "Dedicated Server‎"
            ),
            "class" => "selectpicker show-menu-arrow show-tick",
            "value" => array("")
        ));
        
        $this->addElement('text', 'name', array(
            'required' => 'true',
            'jsLabel' => 'node name',
            'label' => 'node name *',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'ipAddress', array(
            'jsLabel' => 'ip address',
            'label' => 'ip address *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan',
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
        $this->addElement('text', 'mazeUser', array(
            'jsLabel' => 'username',
            'label' => 'username *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));

        $this->setElementDecorators($this->_elementDecorators);
    }

    /**
     * adds additional field elements for the given node 
     * 
     * @param Core_Model_ValueObject_Node $node
     * @return Core_Form_Node
     */
    public function setAdditionalFields(Core_Model_ValueObject_Node $node)
    {
        $additionalFieldsData = $node->getData('additionalFields');
        if (is_array($additionalFieldsData) && !empty($additionalFieldsData)) {
            $this->addAdditionalFields('additionalFields', $additionalFieldsData);
        }

        return $this;
    }

    /**
     * Set default values for elements
     *
     * Sets values for all elements specified in the array of $defaults.
     *
     * @param  array $defaults
     * @return Core_Form_Node
     */
    public function setDefaults(array $defaults)
    {
        if (isset($defaults["additionalFields"])){
            unset($defaults["additionalFields"]);
        }

        parent::setDefaults($defaults);

        return $this;
    }

}

