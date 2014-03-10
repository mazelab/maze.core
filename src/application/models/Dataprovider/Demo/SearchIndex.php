<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_SearchIndex
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_SearchIndex 
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase
    implements Core_Model_Dataprovider_Interface_SearchIndex
{
    
    /**
     * clears complete search index data
     * 
     * @return boolean
     */
    public function clear()
    {
        return false;
    }
    
    /**
     * deletes a certain index
     * 
     * @param string $category search context in order to assign ids
     * @param string $id
     * @return boolean
     */
    public function deleteIndex($category, $id)
    {
        return false;
    }
    
    /**
     * index the given $data under a certain category and id
     * 
     * @param string $category search context in order to assign ids
     * @param string $id entry id
     * @param array $data
     * @return boolean
     */
    public function index($category, $id, $data)
    {
        return false;
    }

}
