<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Search_All
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Search_All 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'search';

    /**
     * field label for view listings
     */
    CONST KEY_ID = '_id';

    /**
     * field name for search
     */
    CONST KEY_SEARCH = 'search';
    
    /**
     * field label for view listings
     */
    CONST SORT_FIELD = 'headline';

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
     * gets last data set with limit
     * 
     * return should be build like:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     * 
     * @param int $limit
     * @param string $searchTerm
     * @return array
     */
    public function last($limit, $searchTerm = null)
    {
        $sort = array(self::SORT_FIELD => -1);
        $result = array();
        
        $query = array();
        if ($searchTerm) {
            $query[self::KEY_SEARCH] = new MongoRegex("/$searchTerm/i");
        }

        $mongoCursor = $this->_getSearchCollection()->find($query);
        $result['total'] = $total = $mongoCursor->count();
        if($total > $limit) {
            $rest = ($total / $limit) - floor($total / $limit);
            if($rest != 0) {
                $limit = bcmul($rest, $limit);
            }
        }
        
        foreach($mongoCursor->sort($sort)->limit($limit) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $result['data'][$id] = $entry;
        }
        
        return array_reverse($result);
    }
    
    /**
     * gets a certain page
     * 
     * return should be build like:
     * array(
     *  'data' => array(),
     *  'total' => '55'
     * )
     * 
     * @param int $limit
     * @param int $page
     * @param string $searchTerm
     * @return array
     */
    public function page($limit, $page, $searchTerm = null)
    {
        $result = array();
        $query = array();
        
        if ($searchTerm) {
            $query[self::KEY_SEARCH] = new MongoRegex("/$searchTerm/i");
        }
        
        $sort = array(
            self::SORT_FIELD => 1
        );
        
        $mongoCursor = $this->_getSearchCollection()->find($query);
        $result['total'] = $mongoCursor->count();
        
        $skip = ($limit * $page) - $limit;
        foreach($mongoCursor->sort($sort)->skip($skip)->limit($limit) as $id => $entry) {
            $entry[self::KEY_ID] = $id;
            $result['data'][$id] = $entry;
        }
        
        return $result;
    }
    
}
