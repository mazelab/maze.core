<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Search_News
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Search_News
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'news';

    /**
     * field key for id
     */
    CONST KEY_ID = '_id';
    
    /**
     * special message is pinged at the beginning
     */
    CONST TYPE_STICKY = Core_Model_Dataprovider_Core_News::TYPE_STICKY;

    /**
     * field for sorting
     */
    CONST SORT_FIELD = Core_Model_Dataprovider_Core_News::KEY_MODIFED;

    /**
     * gets news collection
     * 
     * @return MongoCollection
     */
    protected function _getNewsCollection()
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
        $result = array();
        $query = array();
        
        if ($searchTerm) {
            $query[self::KEY_TITLE] = new MongoRegex("/$searchTerm/i");
        }
        
        $sort = array(
            self::TYPE_STICKY => 1,
            self::SORT_FIELD  => 1
        );
        
        $mongoCursor = $this->_getNewsCollection()->find($query);
        $result['total'] = $total = $mongoCursor->count();
        if($total > $limit) {
            $rest = ($total / $limit) - floor($total / $limit);
            if($rest != 0) {
                $limit = bcmul($rest, $limit);
            }
        }
        
        foreach($mongoCursor->sort($sort)->limit($limit) as $messageId => $message) {
            $message[self::KEY_ID] = $messageId;
            $result['data'][$messageId] = $message;
        }

        $result['data'] = array_reverse($result['data']);
        return $result;
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
            $query[self::KEY_TITLE] = new MongoRegex("/$searchTerm/i");
        }
        
        $sort = array(
            self::TYPE_STICKY => -1,
            self::SORT_FIELD  => -1
        );
        
        $mongoCursor = $this->_getNewsCollection()->find($query);
        $result['total'] = $mongoCursor->count();
        
        $skip = ($limit * $page) - $limit;
        foreach($mongoCursor->sort($sort)->skip($skip)->limit($limit) as $messageId => $message) {
            $message[self::KEY_ID] = $messageId;
            $result['data'][$messageId] = $message;
        }
        
        return $result;
    }
    
}