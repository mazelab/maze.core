<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Core_News
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Core_News
    extends Core_Model_Dataprovider_Core_Data
    implements Core_Model_Dataprovider_Interface_News
{
    /**
     * collection name
     */
    CONST COLLECTION = "news";

    /**
     * key name id
     */
    CONST KEY_ID = "_id";

    /**
     * key for title of the message
     */
    CONST KEY_TITLE = "title";

    /**
     * key for message tags
     */
    CONST KEY_TAGS = "tags";

    /**
     * key for the last modification date
     */
    CONST KEY_MODIFED = "modifiedDate";

    /**
     * key for message status
     */
    CONST KEY_STATUS = "status";

    /**
     * special message is pinged at the beginning
     */
    CONST TYPE_STICKY = "sticky";

    /**
     * field for sorting
     */
    CONST SORT_FIELD = self::KEY_MODIFED;

    /**
     * init mongo db
     */
    public function __construct()
    {
        parent::__construct();
    }

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
     * adds tag for the given message
     * 
     * @param  string $messageId
     * @param  array $tags
     * @return boolean
     */
    public function addTags($messageId, array $tags)
    {
        $mongoId = new MongoId($messageId);

        $query = array(
            self::KEY_ID => $mongoId
        );

        $newData = array(
            '$set' => array(
                self::KEY_TAGS => $tags
            )
        );

        return $this->_getNewsCollection()->update($query, $newData);
    }

    /**
     * detelte a certain message
     * 
     * @param  string $messageId
     * @return boolean
     */
    public function deleteMessage($messageId)
    {
        $query = array(
            self::KEY_ID => new MongoId($messageId)
        );

        return $this->_getNewsCollection()->remove($query);
    }

    /**
     * returns a certain message
     * 
     * @param  string $messageId
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessage($messageId, $filter = null)
    {
        $query = array(
            self::KEY_ID => new MongoId($messageId)
        );

        if (is_string($filter)){
            $filter = array($filter);
        }

        if (is_array($filter) && !empty($filter)){
            $query[self::KEY_STATUS] = array('$type' => 2, '$in' => $filter);
        }

        if(!($message = $this->_getNewsCollection()->findOne($query)) || empty($message)) {
            return array();
        }

        $message[self::KEY_ID] = (string) $message[self::KEY_ID];

        return $message;
    }

    /**
     * returns a certain message by the title
     * 
     * @param  string $title
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessageByTitle($title, $filter = null)
    {
        $query = array(
            self::KEY_TITLE => $title
        );

        if (!empty($filter)){
            $filter = is_string($filter) ? array($filter) : $filter;
            $query[self::KEY_STATUS] = array('$type' => 2, '$in' => $filter);
        }

        if(!($message = $this->_getNewsCollection()->findOne($query)) || empty($message)) {
            return array();
        }

        $message[self::KEY_ID] = (string) $message[self::KEY_ID];

        return $message;
    }

    /**
     * returns all existing messages
     * @param  null|integer $limit
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessages($limit = null, $filter = null)
    {
        $messages = array();
        $query= array();
        $sort = array(
            self::TYPE_STICKY => -1,
            self::SORT_FIELD => -1
        );
        if (!is_integer($limit)){
            $limit = 0;
        }

        if (is_string($filter)){
            $filter = array($filter);
        }

        if (is_array($filter) && !empty($filter)){
            $query = array_merge(array(
                self::KEY_STATUS => array('$type' => 2, '$in' => $filter)
            ), $query);
        }

        foreach ($this->_getNewsCollection()->find($query)->sort($sort)->limit($limit) as $id => $message){
            $message[self::KEY_ID] = (string) $message[self::KEY_ID];
            $messages[$id] = $message;
        }

        return $messages;
    }

    /**
     * returns all messages with the given tags
     * 
     * @param  array $tags
     * @param  null|integer $limit
     * @return array
     */
    public function getMessagesByTags(array $tags, $limit = null)
    {
        $messages = array();
        $query = array();
        $limit = !is_numeric($limit) ? 0 : $limit;
        $sort  = array(
            self::TYPE_STICKY => -1,
            self::SORT_FIELD  => -1
        );
        
        foreach(array_keys($tags) as $tagId) {
            $query['$or'][] = array(self::KEY_TAGS . ".$tagId" => array('$exists' => 1));
        }

        foreach ($this->_getNewsCollection()->find($query)->sort($sort)->limit($limit) as $messageId => $message){
            $message[self::KEY_ID] = $messageId;
            $messages[$messageId] = $message;
        }

        return $messages;
    }

    /**
     * updates/creates a message dataset
     * 
     * @param  array $data
     * @param  string $messageId
     * @return boolean|string id if an message created
     */
    public function saveMessage(array $data, $messageId = null)
    {
        $mongoId = new MongoId($messageId);
        $query = array(
            self::KEY_ID => $mongoId,
        );

        $newData = array(
            '$set' => $this->_getDatabase()->prepareUpdateDataSet($data)
        );

        if(!$this->_getNewsCollection()->update($query, $newData, array('upsert' => true))) {
            return false;
        }

        return (string) $mongoId;
    }
}