<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Interface_User
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Core_Model_ValueObject_Interface_User
{

    /**
     * activates this instance
     * 
     * @return boolean
     */
    public function activate();
    
    /**
     * deactivates this instance
     * 
     * @return boolean
     */
    public function deactivate();
    
    /**
     * returns the valueBean with the loaded data from data backend
     * 
     * override this for custom bean behavior
     * 
     * @param boolean $new force new bean struct
     * @return MazeLib_Bean
     */
    public function getBean($new = false);
    
    /**
     * returns property of data backend context
     * 
     * property in deepth dissolving
     * 
     * @param string $propertyPath
     * @return mixed
     */
    public function getData($propertyPath = null);
    
    /**
     * returns data backend identification
     * 
     * @return string
     */
    public function getId();
    
    /**
     * returns email from data set
     * 
     * @return string
     */
    public function getEmail();
    
    /**
     * returns the user label
     * 
     * @return string
     */
    public function getLabel();
    
    /**
     * returns status flag if set
     * 
     * @return boolean
     */
    public function getStatus();
    
    /**
     * returns username from data set
     * 
     * @return string
     */
    public function getUsername();
    
    /**
     * loads and sets returned context from data backend into the valueBean 
     * 
     * calls _load
     * 
     * @return boolean actually loaded?
     */
    public function load();
    
    /**
     * saves allready seted Data into the data backend
     * 
     * calls _save
     * 
     * @return boolean
     */
    public function save();
    
    /**
     * sets/adds new data set
     * 
     * additional boolean cast
     * 
     * @param array $data
     * @param boolean $skipUpload skips autoupload
     * @param boolean $skipPasswordEncrypt skips password encrypting
     * @return Core_Model_ValueObject_Interface_User
     */
    public function setData(array $data, $skipUpload = false, $skipPasswordEncrypt = false);
    
    /**
     * set a certain property with a certain value
     * 
     * @param string $propertyPath for MazeLib_Bean
     * @param string $value
     * @return Core_Model_ValueObject
     */
    public function setProperty($propertyPath, $value);
    
    /**
     * removes client avatar and deletes the existing file
     * 
     * @return boolean
     */
    public function removeAvatar();

    /**
     * unsets a certain property
     * 
     * @param string $propertyPath for MazeLib_Bean
     * @return Core_Model_ValueObject
     */
    public function unsetProperty($propertyPath);
    
    /**
     * upload a client avatar
     * 
     * @param string $filename file to receive
     * @return string|null
     */
    public function uploadAvatar($filename);
    
}
