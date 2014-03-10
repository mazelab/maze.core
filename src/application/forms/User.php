<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_User
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_User extends Core_Form_AdditionalInfo
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
        $this->addElement('text', 'email', array(
            'jsLabel' => 'E-mail address:',
            'label' => 'E-mail address *',
            'required' => true,
            'validators' => array(
                array('EmailAddress'),
                new Core_Form_Validate_UniqueEmail
            ),
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'username', array(
            'jsLabel' => 'username',
            'label' => 'username *',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                new Core_Form_Validate_ExistsUsername
            ),
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('file', 'avatar', array(
            'class' => 'jsUserAvatar',
            'style' => 'display:none;',
            'validators' => array(
                array('Size', false, '500kb'),
                array('Count', false, 1),
                array('Extension', false, 'jpg,jpeg,gif,png')
             ),
            'valueDisabled' => true
        ));
        $this->addElement('password', 'password', array(
            'jsLabel' => 'password',
            'label' => 'password',
            'required' => true,
            'validators' => array(
                array('StringLength', NULL, array(4)),
                array('identical', true, array('confirmPassword'))
            )
        ));
        $this->addElement('password', 'confirmPassword', array(
            'label' => 'confirm password',
            'required' => true,
            'ignore' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', NULL, array(4)),
                array('identical', true, array('password'))
            )
        ));
        $this->addElement('text', 'company', array(
            'jsLabel' => 'company',
            'label' => 'company *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'prename', array(
            'jsLabel' => 'prename',
            'required' => true,
            'label' => 'prename *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'surname', array(
            'jsLabel' => 'surname',
            'required' => true,
            'label' => 'surname *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'postcode', array(
            'required' => true,
            'jsLabel' => 'postcode',
            'label' => 'postcode *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'city', array(
            'required' => true,
            'jsLabel' => 'city',
            'label' => 'city *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'street', array(
            'required' => true,
            'jsLabel' => 'street',
            'label' => 'street *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'houseNumber', array(
            'required' => true,
            'jsLabel' => 'no.',
            'label' => 'no. *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));		
        $this->addElement('text', 'phone', array(
            'required' => true,
            'jsLabel' => 'phone',
            'label' => 'phone *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'fax', array(
            'jsLabel' => 'fax',
            'label' => 'fax *',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->addElement('text', 'ipAddress', array(
            'jsLabel' => 'ip adress',
            'label' => 'ip adress',
            'class' => 'jsEditable',
            'helper' => 'formTextAsSpan'
        ));
        $this->setElementDecorators($this->_elementDecorators);
        $this->setElementDecorators(array("file"), array("avatar"));
    }

    public function setDefaults(array $defaults)
    {
        if (isset($defaults["additionalFields"])){
            unset($defaults["additionalFields"]);
        }

        parent::setDefaults($defaults);

        return $this;
    }

    /**
     * adds additional field elements for the given client 
     * 
     * @param Core_Model_ValueObject_Client $client
     * @return Core_Form_User
     */
    public function setAdditionalFieldsClient(Core_Model_ValueObject_Client $client)
    {
        $additionalFieldsData = $client->getData('additionalFields');
        if (is_array($additionalFieldsData) && !empty($additionalFieldsData)) {
            $this->addAdditionalFields('additionalFields', $additionalFieldsData);
        }

        return $this;
    }
    
}

