<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Form_NodeServices
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Form_NodeServices extends Zend_Form
{
    protected $_elementDecorators = array(
        'ViewHelper',
        'TwitterBootstrapError'
    );
    
    /**
     * message when service are addable
     */
    CONST SERVICES_AVAILABLE = "Add new service";
    
    /**
     * message when there are no services
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
     * sets service select depending on the given node
     * 
     * @param Core_Model_ValueObject_Node $node
     * @return Core_Form_NodeServices
     */
    public function setServiceSelect(Core_Model_ValueObject_Node $node)
    {
        $serviceOptions = array();
        
        foreach (Core_Model_DiFactory::getModuleRegistry()->getModules() as $service) {
            if($node->hasService($service->getName())) {
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

