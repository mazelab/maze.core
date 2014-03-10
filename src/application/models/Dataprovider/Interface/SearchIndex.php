<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_SearchIndex
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_SearchIndex
{

    /**
     * index for client search
     */
    CONST CLIENT = 'client';
    
    /**
     * index for domain search
     */
    CONST DOMAIN = 'domain';
    
    /**
     * index for node search
     */
    CONST NODE = 'node';
    
    /**
     * clears complete search index data
     * 
     * @return boolean
     */
    public function clear();

    /**
     * deletes a certain index
     * 
     * @param string $category search context in order to assign ids
     * @param string $id
     * @return boolean
     */
    public function deleteIndex($category, $id);
    
    /**
     * index the given $data under a certain category and id
     * 
     * @param string $category search context in order to assign ids
     * @param string $id entry id
     * @param array $data
     * @return boolean
     */
    public function index($category, $id, $data);
    
}
