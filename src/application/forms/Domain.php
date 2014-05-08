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

