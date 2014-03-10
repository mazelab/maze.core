<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Search_Index
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Search_Index
{

    /**
     * returns current provider
     * 
     * @return Core_Model_Dataprovider_Interface_SearchIndex
     */
    protected function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getSearchIndex();
    }
    
    /**
     * clears complete search index backend
     * 
     * @return boolean
     */
    public function clearIndexes()
    {
        if(!$this->_getProvider()) {
            return false;
        }
        
        return $this->_getProvider()->clear();
    }
    
    /**
     * removes a certain index found by kategory and id
     * 
     * @param string $category search context in order to assign ids
     * @param string $id
     * @return boolean
     */
    public function deleteIndex($category, $id)
    {
        if(!is_string($category) || !$this->_getProvider()) {
            return false;
        }
        
        return $this->_getProvider()->deleteIndex($category, $id);
    }
    
    /**
     * updates a certain serach index
     * 
     * @param string $category search context in order to assign ids
     * @param string $id entry id 
     * @param array $data
     * @return boolean
     */
    public function setSearchIndex($category, $id, array $data) {
        if(!is_string($category) || !is_string($id) || !$this->_getProvider()) {
            return false;
        }
        
        return $this->_getProvider()->index($category, $id, $data);
    }
    
}
