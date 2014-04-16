<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Domain
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Domain extends Core_Model_ServiceObject
{
    
    /**
     * flag to determine if search index should be rebuild after save operation
     * 
     * @var boolean
     */
    protected $_rebuildSearchIndex;

    /**
     * message when save failed
     */
    CONST ERROR_SAVING= 'Something went wrong while saving domain %1$s';

    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Domain
     */
    protected function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getDomain();
    }

    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getDomain($this->getId());
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedData)
    {
        $id = $this->_getProvider()->saveDomain($unmappedData, $this->getId());
        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getName());
            return false;
        }

        $this->_setId($id);
        if($this->_rebuildSearchIndex) {
            $this->_rebuildSearchIndex = false;
            Core_Model_DiFactory::getIndexManager()->setDomain($id);
        }
        
        return $id;
    }
    
    /**
     * returns node name from data set
     * 
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }
    
    /**
     * returns the owner of this domain
     * 
     * @return Core_Model_ValueObject_Client|null
     */
    public function getOwner()
    {
        if (!$this->getData('owner')) {
            return null;
        }

        return Core_Model_DiFactory::getClientManager()->getClient($this->getData('owner'));
    }
    
    /**
     * sets/adds new data set
     * 
     * @param array $data
     * @return Core_Model_ValueObject_Domain
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (array_key_exists('name', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return $this;
    }
    
}
