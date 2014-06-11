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

    /**
     * init additional field elements of given data
     *
     * @param array $data
     * @return Core_Form_Node
     */
    public function _initAdditionalFields(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $additionalFieldsForm = new Zend_Form_SubForm();

        foreach($data as $key => $value) {
            $field = new Zend_Form_SubForm();
            $field->addElement('text', 'value');

            $additionalFieldsForm->addSubForm($field, $key);
        }

        $this->addSubForm($additionalFieldsForm, 'additionalFields');

        return $this;
    }

    /**
     * init services sub forms
     *
     * @param array $services
     * @return Core_Form_Node
     */
    protected function _initServices(array $services)
    {
        $serviceForm = new Zend_Form_SubForm();

        foreach($services as $service => $state) {
            $serviceForm->addElement('checkbox', $service, array(
                'required' => true,
                'checkedValue' => 'true',
                'uncheckedValue' => 'false'
            ));
        }

        $this->addSubForm($serviceForm, 'services');

        return $this;
    }

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
        $this->addElement('text', 'additionalKey', array(
            'required' => true
        ));
        $this->addElement('text', 'additionalValue', array(
            'required' => true
        ));

        $this->setElementDecorators($this->_elementDecorators);
    }

    /**
     * init dynamical content
     *
     * @param array $data
     * @return Core_Form_Node
     */
    public function initDynamicContent(array $data)
    {
        if(array_key_exists('services', $data) && is_array($data['services'])) {
            $this->_initServices($data['services']);
        }
        if(array_key_exists('additionalFields', $data) && is_array($data['additionalFields'])) {
            $this->_initAdditionalFields($data['additionalFields']);
        }

        return $this;
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

