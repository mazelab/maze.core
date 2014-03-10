<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Config
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Config extends Core_Model_ValueObject
{
    /**
     * message when saving failed
     */
    CONST ERROR_SAVING = 'Something went wrong while saving config %1$s';
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Config
     */
    protected function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getConfig();
    }

    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getConfig();
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return boolean
     */
    protected function _save($unmappedContext)
    {
        if (!$this->_getProvider()->saveConfig($unmappedContext, $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getId());
            return false;
        }

        return true;
    }

    /**
     * loads and sets returned context from data backend into the valueBean 
     * 
     * @return boolean
     */
    public function load()
    {
        if ($this->_loaded) {
            return false;
        }

        $this->setLoaded(true);
        if (($data = $this->_load()) && is_array($data)) {
            $this->getBean(true)->setBean($data);
        }

        return true;
    }
    
    /**
     * sets/adds new data set as local data
     * 
     * @param  array $data
     * @return Core_Model_ValueObject_Config
     */
    public function setData(array $data)
    {
        if (isset($data["mail"]["smtp"]["password"])){
            $cryptManager = Core_Model_DiFactory::getCryptManager();
            $data["mail"]["smtp"]["password"] = $cryptManager->encrypt($data["mail"]["smtp"]["password"]);
        }

        return parent::setData($data);
    }
}

