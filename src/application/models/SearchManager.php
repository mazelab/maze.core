<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_SearchManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_SearchManager
{

    /**
     * loaded data from data backend
     * 
     * @var array
     */
    protected $_data;
    
    /**
     * limit of units which will be loaded
     * 
     * @var int
     */
    protected $_limit;    
    
    /**
     * actual page number
     * 
     * @var int
     */
    protected $_page;
    
    /**
     * current position in total
     *
     * @var int
     */
    protected $_position;
    
    /**
     * pager provider
     * 
     * @var Core_Model_Dataprovider_Interface_Search 
     */
    protected $_provider;
    
    /**
     * searchTerm for search load
     * 
     * @var string
     */
    protected $_searchTerm;
    
    /**
     * count of all elements of this search in data backend
     * 
     * @var int
     */
    protected $_total;
    
    /**
     * returns current provider
     * 
     * @return Core_Model_Dataprovider_Interface_Search|null
     */
    protected function _getProvider()
    {
        return $this->_provider;
    }
    
    /**
     * sets count for current search
     * 
     * @param int $count
     * @return Core_Model_SearchManager
     */
    protected function _setCount($count = null)
    {
        if (is_numeric($count)) {
            $this->_count = $count;
        }

        return $this;
    }
    
    /**
     * set data set of search
     * 
     * @param array $data
     * @return Core_Model_SearchManager
     */
    protected function _setData(array $data = null)
    {
        $this->_data = $data;

        return $this;
    }
    
    /**
     * set current position of search set in data backend
     * 
     * @param int $total
     * @return Core_Model_SearchManager
     */
    protected function _setPosition($position = null)
    {
        if (is_numeric($position)) {
            $this->_position = $position;
        }

        return $this;
    }
    
    /**
     * set total amount of search set in data backend
     * 
     * @param int $total
     * @return Core_Model_SearchManager
     */
    protected function _setTotal($total = null)
    {
        if (is_numeric($total)) {
            $this->_total = $total;
        }

        return $this;
    }

    /**
     * returns search context as array
     * 
     * @return array
     */
    public function asArray()
    {
        $result = array(
            'count' => $this->getCount(),
            'data' => $this->getData(),
            'limit' => $this->getLimit(),
            'page' => $this->getPage(),
            'position' => $this->getPosition(),
            'total' => $this->getTotal(),
            'lastPosition' => $this->getLastPosition(),
            'searchTerm' => $this->getSearchTerm()
        );
        
        return $result;
    }
    
    /**
     * loads first entry set
     * 
     * @return Core_Model_SearchManager
     */
    public function first()
    {
        $this->page(1);
        
        return $this;
    }

    /**
     * returns count from current data set
     * 
     * @return int
     */
    public function getCount()
    {
        return count($this->getData());
    }

    /**
     * returns current data set from data backend
     * 
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * returns last position of current search data
     * 
     * @return int|null
     */
    public function getLastPosition()
    {
        if(!$this->getPosition() || !$this->getCount()) {
            return null;
        }
        
        return $this->getPosition() + $this->getCount() - 1;
    }
    
    /**
     * returns current limit for search load
     * 
     * @return int|null
     */
    public function getLimit()
    {
        return $this->_limit;
    }
    
    /**
     * returns current page
     * 
     * @return int
     */
    public function getPage()
    {
        return $this->_page;
    }
    
    /**
     * returns position of current page in total
     * 
     * @return int|null
     */
    public function getPosition()
    {
        return $this->_position;
    }
    
    /**
     * return current search term
     * 
     * @return string|null
     */
    public function getSearchTerm()
    {
        return $this->_searchTerm;
    }

    /**
     * returns total data
     * 
     * @return int|null
     */
    public function getTotal()
    {
        return $this->_total;
    }
    
    /**
     * gets last entry set with limit
     * 
     * requires limit and overwritest current page
     * 
     * @return Core_Model_SearchManager
     */
    public function last()
    {
        if($this->getLimit() && $this->_getProvider()) {
            $result = $this->_getProvider()->last($this->getLimit(), $this->getSearchTerm());
            
            if(array_key_exists('data', $result)) {
                $this->_setData($result['data']);
                $this->_setCount(count($result['data']));
            }
            
            if(array_key_exists('total', $result)) {
                $this->_setTotal($result['total']);
            }
            
            $this->_setPosition(($this->getTotal() - $this->getCount()) + 1);
            $this->setPage(ceil($this->getTotal() / $this->getLimit()));
        }
        
        return $this;
    }
    
    /**
     * loads a certain page
     * 
     * limit must be set
     * 
     * @param int $page
     * @return Core_Model_SearchManager
     */
    public function page($page = null)
    {
        if($page) {
            $this->setPage($page);
        }
        
        if($this->getPage() && $this->getLimit() && $this->_getProvider()) {
            $result = $this->_getProvider()->page($this->getLimit(), $this->getPage(), $this->getSearchTerm());

            if(array_key_exists('data', $result)) {
                $this->_setData($result['data']);
                $this->_setCount(count($result['data']));
            }
            
            if(array_key_exists('total', $result)) {
                $this->_setTotal($result['total']);
            }
            
            if($this->getPage() == 1) {
                $this->_setPosition(1);
            } else {
                $this->_setPosition(($this->getPage() * $this->getLimit()) - $this->getLimit() + 1);
            }
        }
        
        return $this;
    }
    
    /**
     * resets search instance
     * 
     * @return Core_Model_SearchManager
     */
    public function reset()
    {
        $this->_setCount();
        $this->_setData();
        $this->_setTotal();
        $this->setSearchTerm();
        $this->setPage();

        return $this;
    }

    /**
     * set limit for search load
     * 
     * @param int $limit
     * @return Core_Model_SearchManager
     */
    public function setLimit($limit = null)
    {
        if (!$limit || is_numeric($limit)) {
            $this->_limit = $limit;
        }

        return $this;
    }

    /**
     * set page for search load
     * 
     * @param int $page
     * @return Core_Model_SearchManager
     */
    public function setPage($page = null)
    {
        if (!$page || is_numeric($page)) {
            $this->_page = $page;
        }

        return $this;        
    }
    
    /**
     * sets provider
     * 
     * resets instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $provider
     * @return Core_Model_SearchManager
     */
    public function setProvider(Core_Model_Dataprovider_Interface_Search $provider = null)
    {
        $this->reset();
        $this->_provider = $provider;
        
        return $this;
    }
    
    /**
     * set search term for search load
     * 
     * @param string $searchTerm
     * @return Core_Model_SearchManager
     */
    public function setSearchTerm($searchTerm = null)
    {
        if (!$searchTerm || is_string($searchTerm)) {
            $this->_searchTerm = $searchTerm;
        }
        
        return $this;
    }

}
