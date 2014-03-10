<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_AddDomain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_AddDomain extends Zend_Form
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
            '' => 'Choose client'
        );

        foreach ($clientManager->getClients() as $clientId => $client) {
            $optionClients[$clientId] = $client->getLabel();
        }

        $this->addElement('text', 'name', array(
            'required' => true,
            'label' => 'domain name *',
            'validators' => array(
                'hostname'
            )
        ));
        $this->addElement('text', 'procurementplace', array(
            'label' => 'procurement place'
            
        ));
        $this->addElement('select', 'owner', array(
            'required' => true,
            'label' => 'domain of client *',
            'class' => 'selectpicker show-menu-arrow show-tick',
            'multiOptions' => $optionClients
        ));

        $this->setElementDecorators($this->_elementDecorators);
    }
    
    public function showOnlyDomainAndDisableSelectbox($domainId)
    {
        if (!is_string($domainId)){
            return false;
        }

        $this->setDefault("owner", $domainId);
        foreach($this->getElement("owner")->getMultiOptions() as $optionId => $option){
            if ($optionId != $domainId){
                $this->getElement("owner")->removeMultiOption($optionId);
            }
        }
        $this->getElement("owner")->setAttrib("disabled", "disabled");
    }

}

