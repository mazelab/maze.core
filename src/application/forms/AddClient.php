<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AddClient
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AddClient extends Core_Form_AddAdmin
{
    public function init()
    {
        parent::init();

        $this->addElement('text', 'company', array(
            'label' => 'company *'
        ));
        $this->addElement('text', 'prename', array(
            'required' => true,
            'label' => 'prename *'
        ));
        $this->addElement('text', 'surname', array(
            'required' => true,
            'label' => 'surname *'
        ));
        $this->addElement('text', 'street', array(
            'required' => true,
            'label' => 'street *'
        ));
        $this->addElement('text', 'houseNumber', array(
            'required' => true,
            'label' => 'no. *'
        ));
        $this->addElement('text', 'postcode', array(
            'required' => true,
            'label' => 'postcode *'
        ));
        $this->addElement('text', 'city', array(
            'required' => true,
            'label' => 'city *'
        ));
        $this->addElement('text', 'phone', array(
            'required' => true,
            'label' => 'phone *'
        ));
        $this->addElement('text', 'fax', array(
            'label' => 'fax *'
        ));
        $this->addElement("select", "status", array(
            "label" => "the client is",
            "multiOptions" => array(
                "0" => "deactivated",
                "1" => "activated"
            ),
            "class" => "selectpicker show-menu-arrow show-tick",
            "value" => array(1)
        ));
        $this->setElementDecorators($this->_elementDecorators);
    }
    
}

