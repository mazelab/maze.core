<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Admin
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Admin 
    extends Core_Model_ValueObject
    implements Core_Model_ValueObject_Interface_User
{

    /**
     * message when saving failed
     */
    CONST ERROR_SAVING = 'Something went wrong while saving admin %1$s';    
    
    /**
     * message when upload failed
     */
    CONST ERROR_UPLOAD_FAILED = 'Upload of %1$s failed!';
    
    /**
     * upload path
     */
    CONST UPLOADS_DIR = '/data/uploads/avatar/';
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_Admin
     */
    public function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getAdmin();
    }
    
    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getAdmin($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveAdmin($unmappedContext, $this->getId());
        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getUsername());
            return false;
        }
        
        $this->_setId($id);
        
        return $id;
    }
    
    /**
     * activates this instance
     * 
     * @return boolean
     */
    public function activate()
    {
        if (!$this->setData(array('status' => true))->save()) {
            return false;
        }
            
        return true;
    }

    /**
     * deactivates this instance
     * 
     * @return boolean
     */
    public function deactivate()
    {
        if (!$this->setData(array('status' => false))->save()) {
            return false;
        }

        return true;
    }
    
    /**
     * returns email from data set
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }
    
    /**
     * returns the admin label
     * 
     * @return string
     */
    public function getLabel()
    {
        return $this->getData('username');
    }
    
    /**
     * returns status flag if set
     * 
     * @return boolean
     */
    public function getStatus()
    {
        return $this->getData('status');
    }
    
    /**
     * returns username from data set
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->getData('username');
    }
    
    /**
     * sets/adds new data set
     * 
     * additional boolean cast
     * 
     * @param array $data
     * @param boolean $skipUpload skips autoupload
     * @param boolean $skipPasswordEncrypt skips password encrypting
     * @return Core_Model_ValueObject_Admin
     */
    public function setData(array $data, $skipUpload = false, $skipPasswordEncrypt = false)
    {
        if (array_key_exists('password', $data) && !$skipPasswordEncrypt) {
                $data['password'] = md5($data['password']);
        }
        
        if (array_key_exists('avatar', $data) && !$skipUpload) {
                $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }
        
        return parent::setData($data);
    }
    
    /**
     * removes admin avatar and deletes the existing file
     * 
     * @return boolean
     */
    public function removeAvatar()
    {
        $fileManager = Core_Model_DiFactory::newFileManager();

        $avatar = APPLICATION_PATH . "/.." . self::UPLOADS_DIR .$this->getData("avatar");
        if(!file_exists($avatar) || $fileManager->fileDelete($avatar)){
            return true;
        }

        return false;
    }
    
    /**
     * upload a admin avatar
     * 
     * @param string $filename file to receive
     * @return string|null
     */
    public function uploadAvatar($filename)
    {
        $fileManager = Core_Model_DiFactory::newFileManager();
        $destination = APPLICATION_PATH . "/.." . self::UPLOADS_DIR;
        
        $formatImage = "%s.%s";
        if (preg_match("/.+base64\,/", $filename) !== 0) {
            $renameImage = sprintf($formatImage, $this->getId(), "gif");
            
            if ($fileManager->base64DecodeToFile($destination . $renameImage, $filename) !== false) {
                $upload = $renameImage;
            }
        }else if ($fileManager->receiveHttpFileInfo($filename)){
            $upload = $fileManager->uploadFile($filename, $destination
                    , sprintf($formatImage, $this->getId()
                    , pathinfo($filename, PATHINFO_EXTENSION)));
        }

        $fileManager->imageResize($destination. $upload, 200);
        $renamed = sprintf($formatImage, pathinfo($upload, PATHINFO_FILENAME), "gif");
        
        if ($upload && rename($destination. $upload, $destination. $renamed)) {
            if ($this->getData("avatar") != $renamed) {
                $this->removeAvatar();
            }
            
            return $renamed;
        }

        Core_Model_DiFactory::getMessageManager()
                ->addError(self::ERROR_UPLOAD_FAILED, $filename);

        return null;
    }
    
}
