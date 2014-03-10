<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_DomainServices
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_DomainServices extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    /**
     * message when services are addable
     */
    CONST SERVICES_AVAILABLE = "Add new service";
    
    /**
     * message when services aren't available
     */
    CONST SERVICES_NOT_AVAILABLE = "No services available";

    public function init()
    {
        $this->addPrefixPath('MazeLib_Form_Decorator_', 'MazeLib/Form/Decorator/', 'decorator');
        
        $this->addElement('select', 'service', array(
            'require' => true,
            'label' => self::SERVICES_AVAILABLE,
            "class" => "selectpicker show-menu-arrow show-tick",
            'value' => array("")
        ));

        $this->setElementDecorators($this->_elementDecorators);
    }
    
    /**
     * sets unassigned services in service select
     * 
     * @param Core_Model_ValueObject_Domain $domain
     * @return Core_Form_DomainServices
     */
    public function setServiceSelect(Core_Model_ValueObject_Domain $domain)
    {
        $serviceOptions = array();
        
        foreach (Core_Model_DiFactory::getModuleRegistry()->getModules() as $service) {
            if($domain->hasService($service->getName())) {
                continue;
            }
            
            $serviceOptions[$service->getName()] = $service->getLabel();
        }
        
        if (count($serviceOptions)){
            $serviceOptions[""] = self::SERVICES_AVAILABLE;
        } else {
            $serviceOptions[""] = self::SERVICES_NOT_AVAILABLE;
            $this->getElement('service')->setAttrib("disabled", "disabled");
        }
        
        $this->getElement('service')->setMultiOptions($serviceOptions);
        
        return $this;
    }

}

