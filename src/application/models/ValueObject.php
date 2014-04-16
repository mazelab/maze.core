<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject
{

    /**
     * status if data was allready loaded
     * 
     * @var boolean
     */
    protected $_loaded = false;

    /**
     * data backend identification
     * 
     * @var string
     */
    protected $_id;

    /**
     * value container
     * 
     * @var MazeLib_Bean
     */
    protected $_valueBean;

    /**
     * sets instance id
     * 
     * @param string $id data backend id
     */
    public function __construct($id = null)
    {
        if ($id) {
            $this->_setId($id);
        }
    }

    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }

    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * override it with your own loading methods
     * 
     * @return array
     */
    protected function _load()
    {
        return array();
    }

    /**
     * saves context into data backend with a provider
     * if successful it returns the id of the data set which was saved
     * 
     * override it with your own saving methods
     * 
     * @return string $id data backend identification
     */
    protected function _save($unmappedData)
    {
        return false;
    }

    /**
     * sets object id for data backend identification
     * 
     * @param string $id
     * @return Core_Model_ValueObject
     */
    protected function _setId($id)
    {
        if (is_string($id) || is_numeric($id)) {
            $this->_id = (string) $id;
        }

        return $this;
    }

    /**
     * adds a additional field
     *
     * @param string $key
     * @param string $value
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($key, $value)
    {
        if (!is_string($key) || !is_string($value)) {
            return false;
        }

        $additionalField = array(
            "label" => $key,
            "value" => $value
        );

        $this->setData(array('additionalFields' => array(md5($key) => $additionalField)));

        return md5($key);
    }

    /**
     * deletes a additional field
     *
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($key)
    {
        if(!is_string($key) || !$this->getData('additionalFields/' . $key)) {
            return true;
        }

        return $this->unsetProperty('additionalFields/' . $key);
    }

    /**
     * returns the valueBean with the loaded data from data backend
     * 
     * override this for custom bean behavior
     * 
     * @param boolean $new force new bean struct
     * @return MazeLib_Bean
     */
    public function getBean($new = false)
    {
        if ($new || !$this->_valueBean || !$this->_valueBean instanceof MazeLib_Bean) {
            $this->_valueBean = new MazeLib_Bean();
        }

        $this->load();

        return $this->_valueBean;
    }
    
    /**
     * returns existing conflicts/certain conflicts of the value bean structure
     * 
     * @param int $status conflicted with a certain status
     * @return array
     */
    public function getConflicts($status = null)
    {
        return $this->getBean()->getConflicts($status);
    }

    /**
     * returns property of data backend context
     * 
     * property in deepth dissolving
     * 
     * @param string $propertyPath
     * @return mixed
     */
    public function getData($propertyPath = null)
    {
        if (!$propertyPath) {
            return $this->getBean()->asDeepArray();
        }

        return $this->getBean()->getLocalProperty($propertyPath);
    }

    /**
     * returns data backend identification
     * 
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * returns remote properties of data backend context
     * 
     * @param string $propertyPath
     * @return mixed
     */
    public function getRemoteData($propertyPath = null)
    {
        if (!$propertyPath) {
            return $this->getBean()->getRemoteData();
        }
        
        return $this->getBean()->getRemoteProperty($propertyPath);
    }

    /**
     * loads and sets returned context from data backend into the valueBean 
     * 
     * calls _load
     * 
     * @return boolean actually loaded?
     */
    public function load()
    {
        if ($this->_loaded) {
            return false;
        }
        
        if(!$this->getId()) {
            $this->setLoaded(true);
            return false;
        }
        
        $this->setLoaded(true);
        if (($data = $this->_load()) && is_array($data)) {
            $this->getBean(true)->setBean($data);
        }

        return true;
    }

    /**
     * saves allready seted Data into the data backend
     * 
     * calls _save
     * 
     * @return boolean
     */
    public function save()
    {
        $this->setData(array(
            'modified' => time(),
            'modifiedReadable' => Zend_Date::now()->get(Zend_Date::ISO_8601)
        ));
        
        $unmappedData = $this->getBean()->asDeepArray(true);
        if(array_key_exists('_id', $unmappedData)) {
            unset($unmappedData['_id']);
        }

        if (!($id = $this->_save($unmappedData, $this->getId()))) {
            return false;
        }

        $this->_setId($id);
        
        return true;
    }

    /**
     * sets/adds new data set as local data
     * 
     * @param array $data
     * @return Core_Model_ValueObject
     */
    public function setData(array $data)
    {
        $this->getBean()->setLocalData($data);

        return $this;
    }

    /**
     * sets/adds new data set as remote data
     * 
     * @param array $data
     * @return Core_Model_ValueObject
     */
    public function setRemoteData($data)
    {
        $this->getBean()->setRemoteData($data);

        return $this;
    }
    
    /**
     * sets loaded property to an boolean value
     * 
     * determines that context wont be queried from db until there were changes
     * 
     * @param boolean $boolean
     * @return Core_Model_ValueObject
     */
    public function setLoaded($boolean)
    {
        if(is_bool($boolean)) {
            $this->_loaded = $boolean;
        }
        
        return $this;
    }
    
    /**
     * set a certain property with a certain value
     * 
     * @param string $propertyPath for MazeLib_Bean
     * @param string $value
     * @return Core_Model_ValueObject
     */
    public function setProperty($propertyPath, $value)
    {
        $this->getBean()->setLocalProperty($propertyPath, $value);
        
        return $this;
    }

    /**
     * unsets a certain property
     * 
     * @param string $propertyPath for MazeLib_Bean
     * @return Core_Model_ValueObject
     */
    public function unsetProperty($propertyPath)
    {
        $this->getBean()->unsetProperty($propertyPath);
        
        return $this;
    }
    
}
