<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_NewsManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_NewsManager
{
    /**
     * key name id
     */
    CONST KEY_ID = "_id";

    /**
     * key for the creation date
     */
    CONST KEY_CREATED = "creationDate";

    /**
     * key for the last modification date
     */
    CONST KEY_MODIFED = "modifiedDate";

    /**
     * key for the online since
     */
    CONST KEY_ONSINCE = "onlineSince";

    /**
     * key for message status
     */
    CONST KEY_STATUS = "status";

    /**
     * key for message tags
     */
    CONST KEY_TAGS = "tags";

    /**
     * special message is pinged at the beginning
     */
    CONST TYPE_STICKY = "sticky";
    
    /**
     * message status for published news
     */
    CONST STATUS_PUBLIC = "public";

    /**
     * message status for draft news
     */
    CONST STATUS_DRAFT = "draft";

    /**
     * message status for closed news
     */
    CONST STATUS_CLOSED = "closed";

    /**
     * converts the message timestamp to readable dates
     * 
     * @param  array $message
     * @return array
     */
    protected function _timestampToReadable($message)
    {
        if (key_exists(self::KEY_CREATED, $message)){
            $creation = new Zend_Date($message[self::KEY_CREATED]);
            $message[self::KEY_CREATED. "Readable"] = $creation->get(Zend_Date::DATE_LONG);
        }

        if (key_exists(self::KEY_MODIFED, $message)){
            $modified = new Zend_Date($message[self::KEY_MODIFED]);
            $message[self::KEY_MODIFED. "Readable"] = $modified->get("M. MMMM yyyy HH:mm:ss");
        }

        if (key_exists(self::KEY_ONSINCE, $message)){
            $onSince  = new Zend_Date($message[self::KEY_ONSINCE]);
            $message[self::KEY_ONSINCE. "Readable"] = $onSince->get(Zend_Date::DATE_LONG);
        }

        return $message;
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
        if (key_exists(self::KEY_TAGS, $tags)){
            $tags = $tags[self::KEY_TAGS];
        }

        if (($message = $this->getMessage($messageId)) && !empty($tags)){
            if (!key_exists(self::KEY_TAGS, $message)){
                $message[self::KEY_TAGS] = array();
            }

            foreach ($tags as $index => $tag){
                $tagsId = md5($tag);
                if (empty($tag) && isset($message[self::KEY_TAGS][$index])){
                    unset($message[self::KEY_TAGS][$index]);
                    continue;
                }

                $message[self::KEY_TAGS][$tagsId] = $tag;
            }

            if ($this->getProvider()->addTags($messageId, $message[self::KEY_TAGS])){
                return $tagsId;
            }
        }

        return false;
    }

    /**
     * creates a message
     * 
     * @param  array $data
     * @return boolean
     */
    public function createMessage(array $data)
    {
        if (key_exists(self::KEY_TAGS, $data)){
            $tags = array();

            foreach ($data[self::KEY_TAGS] as $tag){
                if (empty($tag)){
                    continue;
                }
                $tags[md5($tag)] = $tag;
            }
            $data[self::KEY_TAGS] = $tags;
        }

        return $this->getProvider()->saveMessage($data);
    }

    /**
     * detelte a certain message
     * 
     * @param  string $messageId
     * @return boolean
     */
    public function deleteMessage($messageId)
    {
        if ($this->getProvider()->deleteMessage($messageId)){
            return true;
        }

        return false;
    }
    
    /**
     * returns a certain message
     * 
     * @param  string $messageId
     * @param  mixed $filter to filter for a specific status
     * @return array|null
     */
    public function getMessage($messageId, $filter = null)
    {
        $message = $this->getProvider()->getMessage($messageId, $filter);
        if(empty($message) || !key_exists(self::KEY_ID, $message)) {
            return null;
        }

        return $this->_timestampToReadable($message);
    }

    /**
     * returns a certain message by the title
     * 
     * @param  string $title
     * @param  mixed $filter to filter for a specific status
     * @return array|null
     */
    public function getMessageByTitle($title, $filter = null)
    {
        if (!($message = $this->getProvider()->getMessageByTitle($title, $filter))){
            return null;
        }

        return $this->_timestampToReadable($message);
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
        $messages = $this->getProvider()->getMessages($limit, $filter);
        if (empty($messages)){
            return array();
        }

        foreach ($messages as $id => $message){
            $messages[$id] = $this->_timestampToReadable($message);
        }

        return $messages;
    }
    
    /**
     * get messages by the given tags
     * 
     * @param  mixed $tags
     * @param  null|integer $limit
     * @return array
     */
    public function getMessagesByTags($tags, $limit = null)
    {
        if (is_string($tags)){
            $tags = array($tags);
        }

        $tagsHashed = array();
        foreach ((array) $tags as $tag){
            $tagsHashed[md5($tag)] = $tag;
        }

        return $this->getProvider()->getMessagesByTags($tagsHashed, $limit);
    }

    /**
     * @return Core_Model_Dataprovider_Interface_News
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getNews();
    }

    /**
     * updates the dataset of the given message
     * 
     * @param  string $messageId
     * @param  array $data
     * @return boolean
     */
    public function updateMessage($messageId, $data)
    {
        if (!($message = $this->getMessage($messageId)) || empty($data)){
            return false;
        }

        $timenow = time();
        if (key_exists("title", $data) || key_exists("content", $data)){
            $data[self::KEY_MODIFED] = $timenow;
        }

        if (key_exists(self::KEY_ID, $data)){
            unset($data[self::KEY_ID]);
        }

        if (key_exists(self::TYPE_STICKY, $data)){
            $data[self::TYPE_STICKY] = (boolean) $data[self::TYPE_STICKY];
        }

        if (!key_exists(self::KEY_CREATED, $message)){
            $data[self::KEY_CREATED] = $timenow;
        }

        if (isset($data[self::KEY_STATUS]) && $data[self::KEY_STATUS] == self::STATUS_PUBLIC){
            $data[self::KEY_ONSINCE] = $timenow;
            $data[self::KEY_STATUS]  = self::STATUS_PUBLIC;
        }else {
            unset($data[self::KEY_ONSINCE]);
        }

        return $this->getProvider()->saveMessage($data, $message[self::KEY_ID]);
    }
    
}