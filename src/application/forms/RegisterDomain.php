<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_RegisterDomain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_RegisterDomain extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    public function init()
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        
        $clientManager = Core_Model_DiFactory::getClientManager();
        $optionClients = array(
            '' => ''
        );

        foreach ($clientManager->getClients() as $clientId => $client) {
            $optionClients[$clientId] = $client->getLabel();
        }

        $this->addElement('text', 'name', array(
            'required' => true,
            'label' => 'domain name *',
            'readonly' => 'readonly',
            'validators' => array(
                'hostname'
            )
        ));
        
        $this->addElement('select', 'owner', array(
            'required' => true,
            'label' => 'domain of client *',
            'class' => 'selectpicker show-menu-arrow show-tick',
            'multiOptions' => $optionClients
        ));
        
        $this->setElementDecorators($this->_elementDecorators);
    }
    
}

