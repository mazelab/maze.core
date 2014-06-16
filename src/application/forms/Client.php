<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_Client
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_Client extends Zend_Form
{

    /**
     * init services sub forms
     *
     * @param array $services
     * @return Core_Form_Client
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
        $this->addElement('text', 'company');
        $this->addElement('text', 'fax');

        $this->addElement('text', 'prename', array(
            'required' => true
        ));
        $this->addElement('text', 'surname', array(
            'required' => true
        ));
        $this->addElement('text', 'postcode', array(
            'required' => true
        ));
        $this->addElement('text', 'city', array(
            'required' => true
        ));
        $this->addElement('text', 'street', array(
            'required' => true
        ));
        $this->addElement('text', 'houseNumber', array(
            'required' => true
        ));		
        $this->addElement('text', 'phone', array(
            'required' => true
        ));

        $this->addElement('text', 'email', array(
            'required' => true,
            'validators' => array(
                array('EmailAddress'),
                new Core_Form_Validate_UniqueEmail
            )
        ));
        $this->addElement('text', 'username', array(
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                new Core_Form_Validate_ExistsUsername
            )
        ));
//        $this->addElement('file', 'avatar', array(
//            'required' => false,
//            'validators' => array(
//                array('Size', false, '500kb'),
//                array('Count', false, 1),
//                array('Extension', false, 'jpg,jpeg,gif,png')
//            ),
//            'valueDisabled' => true
//        ));
        $this->addElement('password', 'password', array(
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4))
            )
        ));
        $this->addElement('password', 'confirmPassword', array(
            'ignore' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', NULL, array(4)),
                array('identical', true, array('password'))
            )
        ));

    }

    /**
     * init dynamical content
     *
     * @param array $data
     * @return Core_Form_Client
     */
    public function initDynamicContent(array $data)
    {
        if(array_key_exists('services', $data) && is_array($data['services'])) {
            $this->_initServices($data['services']);
        }

        return $this;
    }

}

