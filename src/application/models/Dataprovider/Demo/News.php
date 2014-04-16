<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_News
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_News
    extends Core_Model_Dataprovider_Demo_SessionAsDatabase 
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
     * key for the creation date
     */
    CONST KEY_CREATED = "creationDate";

    /**
     * key for the last modification date
     */
    CONST KEY_MODIFED = "modifiedDate";

    /**
     * key for message status
     */
    CONST KEY_STATUS = "status";

    /**
     * message status key for publication
     */
    CONST STATUS_PUBLIC = "public";

    /**
     * special message is pinged at the beginning
     */
    CONST TYPE_STICKY = "sticky";

    /**
     * key for message tags
     */
    CONST TAG_FIELD = "tags";

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
     * adds tags for the given message
     * 
     * @param  string $messageId
     * @param  array $tags
     * @return boolean
     */
    public function addTags($messageId, array $tags)
    {
        if (empty($tags)){
            return false;
        }

        return $this->saveMessage(array(self::TAG_FIELD => $tags), $messageId);
    }

    /**
     * creates a message
     * 
     * @param  array $data
     * @return string id of created message 
     */
    public function createMessage(array $data)
    {
        if (array_key_exists(self::KEY_ID, $data)){
            unset($data[self::KEY_ID]);
        }

        $message = $data;
        $message[self::KEY_ID] = rand(45, 645434);

        $collection[$message[self::KEY_ID]] = $message;
        $this->_setCollection(self::COLLECTION, $collection);

        return $message[self::KEY_ID];
    }

    /**
     * detelte a certain message
     * 
     * @param  string $messageId
     * @return boolean
     */
    public function deleteMessage($messageId)
    {
        $collection = $this->_getNewsCollection();

        if (isset($collection[$messageId])){
            unset($collection[$messageId]);
            $this->_setCollection(self::COLLECTION, $collection);

            return true;
        }

        return false;
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
        if (!is_array($filter) && is_string($filter)){
            $filter = array($filter);
        }

        foreach ($this->_getNewsCollection() as $message) {
            if ((!empty($filter) && !isset($message[self::KEY_STATUS]))
            || (!empty($filter) && !in_array($message[self::KEY_STATUS], $filter))){
                continue;
            }

            if (isset($message[self::KEY_ID]) && $message[self::KEY_ID] == $messageId) {
                return $message;
            }
        }

        return array();
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
        foreach ($this->_getNewsCollection() as $message){
            if (isset($message[self::KEY_TITLE]) && $message[self::KEY_TITLE] == $title){
                if (isset($message[self::KEY_ID])){
                    return $message;
                }
            }
        }

        return array();
    }

    /**
     * returns all existing messages
     * 
     * @param  null|integer $limit
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessages($limit = null, $filter = null)
    {
        $messages = array();
        if (!is_array($filter) && is_string($filter)){
            $filter = array($filter);
        }
        
        foreach ($this->_getNewsCollection() as $message){
            if ((!empty($filter) && !isset($message[self::KEY_STATUS]))
            || (!empty($filter) && !in_array($message[self::KEY_STATUS], $filter))){
                continue;
            }

            if (is_integer($limit) && $limit <= sizeof($messages)){
                continue;
            }
            array_push($messages, $message);
        }

        return $messages;
    }

    /**
     * returns messages with the given tags
     * 
     * @param  array $tags
     * @param  null|integer $limit
     * @return array
     */
    public function getMessagesByTags(array $tags, $limit = null)
    {
        $messages = array();

        foreach ($this->_getNewsCollection() as $message){
            if (is_integer($limit) && sizeof($messages) >= $limit){
                break;
            }

            if (!is_array($message["tags"])){
                continue;
            }

            foreach ($tags as $tag){
                if (in_array($tag, $message["tags"])){
                    array_push($messages, $message);
                }
            }
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
        $collection = $this->_getCollection(self::COLLECTION);
        
        if (is_null($messageId)){
            return $this->createMessage($data);
        }
        
        if (($message = $this->getMessage($messageId))){
            $message[self::KEY_ID] = $messageId;

            $collection[$messageId] = array_merge($message, $data);
            $this->_setCollection(self::COLLECTION, $collection);

            return true;
        }

        return false;
    }
}
