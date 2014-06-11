<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Domain extends Core_Form_AdditionalInfo
{
    protected $_elementDecorators = array(
        'ViewHelper'
    );

    /**
     * init additional field elements of given data
     *
     * @param array $data
     * @return Core_Form_Domain
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
     * @return Core_Form_Domain
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
        $this->addElement('text', 'name', array(
            'required' => 'true',
            'jsLabel' => 'domain name',
            'label' => 'domain name',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'owner', array(
            'required' => 'true',
            'label' => 'Client',
            'helper' => 'formTextAsSpan'
        ));
         $this->addElement('text', 'procurement', array(
            'label' => 'procurement place',
            'jsLabel' => 'procurement place',
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
     * @return Core_Form_Domain
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
     * adds additional field elements for the given domain 
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @return Core_Form_Domain
     */
    public function setAdditionalFields(Core_Model_ValueObject_Domain $domain)
    {
        $additionalFieldsData = $domain->getData('additionalFields');
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
     * @return Core_Form_Domain
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

