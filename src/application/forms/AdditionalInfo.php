<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AdditionalInfo
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AdditionalInfo extends Zend_Form
{
    /**
     * adds additional fields to the form
     * 
     * @param string $elementName index/referer for additionalFields
     * @param array $data
     * @return mixed
     */
    public function addAdditionalFields($elementName, array $data)
    {
        if (empty($data)) {
            return false;
        }

        $this->addSubForm(new Zend_Form, $elementName);
        $this->getSubForm($elementName)->setOptions(array('isArray' => true));

        foreach ($data as $id => $field) {
            $this->getSubForm($elementName)->addElement('text', (string) $id, array(
                'label' =>  $field["label"],
                'class' => 'jsEditableAdditionalFields',
                'helper' => 'formTextAsSpan',
                'value' => $field["value"]
            ))->setElementDecorators(array(
                'ViewHelper'
            ));
        }
    }

    public function init()
    {
        $this->addElement('text', 'additionalKey', array(
            'required' => true
        ));
        $this->addElement('textarea', 'additionalValue', array(
            'required' => true
        ));
    }

}

