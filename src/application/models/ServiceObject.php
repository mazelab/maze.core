<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ServiceObject
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ServiceObject extends Core_Model_ValueObject
{
    /**
     * adds a certain service in data backend
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function addService($service)
    {
        if (!$this->getId()) {
            return false;
        }
        
        if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($service))) {
            return false;
        }
        
        $dataSet = array(
            'services' => array(
                $service->getName() => array(
                    'name' => $service->getName(),
                    'label' => $service->getLabel()
                )
            )
        );
        
        return $this->setData($dataSet)->save();
    }
    
    /**
     * returns all services
     * 
     * @return array
     */
    public function getServices()
    {
        $services = array();
        
        if(!is_array($this->getData('services'))) {
            return array();
        }
        
        foreach(array_keys($this->getData('services')) as $serviceName) {
            if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName))) {
                continue;
            }
            
            $services[$service->getName()] = $service->getModuleConfig();
        }
        
        return $services;
    }

    /**
     * checks if the given service is allready registered
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function hasService($service)
    {
        $services = $this->getServices();

        if (array_key_exists($service, $services)) {
            return true;
        }

        return false;
    }
    
    /**
     * removes a certain service in data backend
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function removeService($service)
    {
        if(!$this->hasService($service)) {
            return false;
        }

        if (count($this->getData("services")) == 1){
            return $this->unsetProperty("services")->save();
        }else {
            return $this->unsetProperty("services/$service")->save();
        }
    }

}
