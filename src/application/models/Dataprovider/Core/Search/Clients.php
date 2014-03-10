<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Search_Clients
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Search_Clients 
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * collection name
     */
    CONST COLLECTION = 'user';

    /**
     * value for clients in KEY_GROUP
     */
    CONST GROUP = Core_Model_UserManager::GROUP_CLIENT;
    
    /**
     * field key which contains company name
     */
    CONST KEY_COMPANY = 'company';
    
    /**
     * field key for family name
     */
    CONST KEY_FAMILY_NAME = 'surname';
    
    /**
     * field key for given name
     */
    CONST KEY_GIVEN_NAME = 'prename';
    
    /**
     * field key for group flag
     */
    CONST KEY_GROUP = 'group';

    /**
     * field key for id
     */
    CONST KEY_ID = '_id';
    
    /**
     * field for sorting
     */
    CONST SORT_FIELD = 'label';
    
    /**
     * set mongodb index
     */
    public function __construct() {
        parent::__construct();
        
        $this->_getUserCollection()->ensureIndex(array(
            self::KEY_GROUP => 1
        ));
    }
    
    /**
     * returns an regex query for the OR operation
     * 
     * @param  string $searchTerm
     * @return array
     */
    protected function _getSearchQuery($searchTerm)
    {
        return array(
            array(self::KEY_COMPANY => new MongoRegex("/$searchTerm/i")),
            array(self::KEY_FAMILY_NAME => new MongoRegex("/$searchTerm/i")),
            array(self::KEY_GIVEN_NAME => new MongoRegex("/$searchTerm/i"))
        );
    }

    /**
     * gets user collection
     * 
     * @return MongoCollection
     */
    protected function _getUserCollection()
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
        $query = array(
            self::KEY_GROUP => static::GROUP
        );
        
        if ($searchTerm) {
            $query['$or'] = $this->_getSearchQuery($searchTerm);
        }
        
        $sort = array(
            static::SORT_FIELD => -1
        );
        
        $mongoCursor = $this->_getUserCollection()->find($query);
        $result['total'] = $total = $mongoCursor->count();
        if($total > $limit) {
            $rest = ($total / $limit) - floor($total / $limit);
            if($rest != 0) {
                $limit = bcmul($rest, $limit);
            }
        }
        
        foreach($mongoCursor->sort($sort)->limit($limit) as $clientId => $client) {
            $client[self::KEY_ID] = $clientId;
            $result['data'][$clientId] = $client;
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
        $query = array(
            self::KEY_GROUP => static::GROUP
        );
        
        if ($searchTerm) {
            $query['$or'] = $this->_getSearchQuery($searchTerm);
        }
        
        $sort = array(
            static::SORT_FIELD => 1
        );
        
        $mongoCursor = $this->_getUserCollection()->find($query);
        $result['total'] = $mongoCursor->count();
        
        $skip = ($limit * $page) - $limit;
        foreach($mongoCursor->sort($sort)->skip($skip)->limit($limit) as $clientId => $client) {
            $client[self::KEY_ID] = $clientId;
            $result['data'][$clientId] = $client;
        }
        
        return $result;
    }
    
}
