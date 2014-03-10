<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_Search_Admins
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_Search_Admins 
    extends Core_Model_Dataprovider_Core_Search_Clients
    implements Core_Model_Dataprovider_Interface_Search
{
    
    /**
     * value for administrators in KEY_GROUP
     */
    CONST GROUP = Core_Model_UserManager::GROUP_ADMIN;

    /**
     * field key which contains email
     */
    CONST KEY_EMAIL = 'email';
    
    /**
     * field key for username
     */
    CONST KEY_USERNAME = 'username';

    /**
     * field for sorting
     */
    CONST SORT_FIELD = self::KEY_USERNAME;
    
    protected function _getSearchQuery($searchTerm)
    {
        return array(
            array(self::KEY_USERNAME => new MongoRegex("/$searchTerm/i")),
            array(self::KEY_EMAIL => new MongoRegex("/$searchTerm/i"))
        );
    }
    
}
