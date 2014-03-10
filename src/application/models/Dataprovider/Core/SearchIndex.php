<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_SearchIndex
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_SearchIndex 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_SearchIndex
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'search';
    
    /**
     * field label for ids of an entry
     */
    CONST KEY_ENTRY_ID = 'id';
    
    /**
     * field name for type
     */
    CONST KEY_CATEGORY = 'category';
    
    /**
     * gets search collection
     * 
     * @return MongoCollection
     */
    public function _getSearchCollection()
    {
        return $this->_getCollection(self::COLLECTION);
    }
    
    /**
     * clears complete search index data
     * 
     * @return boolean
     */
    public function clear()
    {
        $result = $this->_getSearchCollection()->drop();
        if(!is_array($result) || !key_exists('ok', $result) || $result['ok'] != true) {
            return false;
        }
        
        return true;
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
        $query = array(
            self::KEY_CATEGORY => $category,
            self::KEY_ENTRY_ID => $id
        );
        
        $options = array(
            'upsert' => true
        );
        
        $data[self::KEY_CATEGORY] = $category;
        $data[self::KEY_ENTRY_ID] = $id;
        
        return $this->_getSearchCollection()->update($query, $data, $options);
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
        $query = array(
            self::KEY_CATEGORY => $category,
            self::KEY_ENTRY_ID => $id
        );
        
        $options = array(
            "j" => true
        );
        
        return $this->_getSearchCollection()->remove($query, $options);
    }

}
