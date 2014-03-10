<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Interface_News
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_Dataprovider_Interface_News
{
    /**
     * adds tags for the given message
     * 
     * @param  string $messageId
     * @param  array $tags
     * @return boolean
     */
    public function addTags($messageId, array $tags);

    /**
     * detelte a certain message
     * 
     * @param  string $messageId
     * @return boolean
     */
    public function deleteMessage($messageId);

    /**
     * returns a certain message
     * 
     * @param  string $messageId
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessage($messageId, $filter = null);

    /**
     * returns a certain message by the title
     * 
     * @param  string $title
     * @param  mixed $filter to filter for a specific status
     * @return array|null
     */
    public function getMessageByTitle($title, $filter = null);

    /**
     * returns all existing messages
     * 
     * @param  null|integer $limit
     * @param  mixed $filter to filter for a specific status
     * @return array
     */
    public function getMessages($limit = null, $filter = null);

    /**
     * returns messages with the given tags
     * 
     * @param  array $tags
     * @param  null|integer $limit
     * @return array
     */
    public function getMessagesByTags(array $tags, $limit = null);

    /**
     * updates/creates a message dataset
     * 
     * @param  array $data
     * @param  string $messageId
     * @return boolean 
     */
    public function saveMessage(array $data, $messageId = null);
}