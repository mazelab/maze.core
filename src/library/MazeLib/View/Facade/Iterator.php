<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_View_Facade_Iterator
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_View_Facade_Iterator 
    extends MazeLib_View_AbstractFacade
    implements Countable,Iterator
{
    /**
     * @var array
     */
    private $_arrayContext;
    /**
     * @var ZendX_Bean
     */
    private $_beanContext = null;

    /**
     * deploys data array into bean object
     * 
     * @param array $data
     * @return boolean 
     */
    protected function _initData($data)
    {
        $this->_beanContext = new ZendX_Bean($data);
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $this->setProperty($key, $value);
        }
        if (null === $this->_arrayContext) {
            $this->_arrayContext = array();
        }
    }

    /**
     * Covers using in string context
     *
     * @return string of classname
     */
    public function __toString()
    {
        return 'ViewIterator';
    }

    /**
     * set given property in the bean and array context
     *
     * @param string $key
     * @param mixed $value
     */
    public function setProperty($key, $value)
    {
        $this->_beanContext->setProperty($key, $value);
        $this->_arrayContext[$key] = $value;
    }

    /**
     * counts all elements
     *
     * @return int
     */
    public function count()
    {
        return count($this->_arrayContext);
    }

    /**
     * returns the current property
     * 
     * @return mixed
     */
    public function current()
    {
        $key = key($this->_arrayContext);
        return $this->getProperty($key);
    }

    /**
     * returns the escaped key of the current property
     *
     * @return mixed
     */
    public function key()
    {
        return $this->_escapeForContext(key($this->_arrayContext), $this->_escapingContext);
    }

    /**
     * sets the pointer to the next property
     */
    public function next()
    {
        next($this->_arrayContext);
    }

    /**
     * sets the pointer to the first property
     */
    public function rewind()
    {
        reset($this->_arrayContext);
    }

    /**
     * validates current property
     * 
     * @return boolean
     */
    public function valid()
    {
        return null !== key($this->_arrayContext);
    }
    
    /**
     * returns a certain raw property
     * 
     * @param string $key
     * @return mixed
     */
    protected function _fetchRawValue($key)
    {
        if ($key === null) {
            return $this->_arrayContext;
        }
        return $this->_beanContext->getProperty(ltrim($key, '/'));
    }

}
