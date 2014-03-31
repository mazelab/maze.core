<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_ValueObject_Client
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_ValueObject_Client 
    extends Core_Model_ValueObject
    implements Core_Model_ValueObject_Interface_User
{

    /**
     * message when saving failed
     */
    CONST ERROR_SAVING = 'Something went wrong while saving client %1$s';
 
    /**
     * message when upload failed
     */
    CONST ERROR_UPLOAD_FAILED = 'Upload of %1$s failed!';
    
    /**
     * upload path
     */
    CONST UPLOADS_DIR = '/data/uploads/avatar/';
    
    /**
     * flag to determine if search index should be rebuild after save operation
     * 
     * @var boolean
     */
    protected $_rebuildSearchIndex;
    
    /**
     * returns data backend provider
     * 
     * @return Core_Model_Dataprovider_Interface_User
     */
    public function _getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getClient();
    }
    
    /**
     * loads context from data backend with a provider
     * returns loaded context as array
     * 
     * @return array
     */
    public function _load()
    {
        return $this->_getProvider()->getClient($this->getId());
    }
    
    /**
     * saves allready seted Data into the data backend
     * 
     * @param array $unmappedData from Bean
     * @return string $id data backend identification
     */
    protected function _save($unmappedContext)
    {
        $id = $this->_getProvider()->saveClient($unmappedContext, $this->getId());
        if (!$id || ($this->getId() && $id !== $this->getId())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ERROR_SAVING, $this->getUsername());
            return false;
        }
        
        $this->_setId($id);
        if($this->_rebuildSearchIndex) {
            $this->_rebuildSearchIndex = false;
            Core_Model_DiFactory::getIndexManager()->setClient($id);
        }
        
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
     * adds a additional field
     * 
     * @param string $key
     * @param string $value
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($key, $value)
    {
        if (!$this->getId() || !is_string($key) || !is_string($value)) {
            return false;
        }

        $additionalField = array(
            "label" => $key,
            "value" => $value
        );

        $this->setData(array('additionalFields' => array(md5($key) => $additionalField)));
        if (!$this->save()){
            return false;
        }

        return md5($key);
    }
    
    /**
     * adds a certain service in data backend
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function addService($service)
    {
        if (!$this->getId()) {
            return false;
        }
        
        if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($service))) {
            return false;
        }
        
        $dataSet = array(
            'services' => array(
                $service->getName() => array(
                    'name' => $service->getName(),
                    'label' => $service->getLabel()
                )
            )
        );
        
        return $this->setData($dataSet)->save();
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
     * deletes a certain additional field from this client in the data backend
     * 
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($key)
    {
        if (!$this->getId() || !is_string($key)) {
            return false;
        }
        
        if(!$this->getData('additionalFields/' . $key)) {
            return true;
        }
        
        return $this->unsetProperty('additionalFields/' . $key)->save();
    }
    
    /**
     * returns all domains of this client
     * 
     * @return array contains Core_Model_Domain
     */
    public function getDomains()
    {
        if (!$this->getId()) {
            return array();
        }

        return Core_Model_DiFactory::getDomainManager()->getDomainsByOwner($this->getId());
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
     * returns the user label
     * 
     * @return string
     */
    public function getLabel()
    {
        return $this->getData('label');
    }
    
    /**
     * returns all client services
     * 
     * @return array
     */
    public function getServices()
    {
        $services = array();
        
        if(!is_array($this->getData('services'))) {
            return array();
        }
        
        foreach(array_keys($this->getData('services')) as $serviceName) {
            if(!($service = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName))) {
                continue;
            }
            
            $services[$service->getName()] = $service->getModuleConfig();
        }
        
        return $services;
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
     * checks if the given service is allready registered
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function hasService($service)
    {
        $services = $this->getServices();

        if (key_exists($service, $services)) {
            return true;
        }

        return false;
    }
    
    /**
     * sets/adds new data set
     * 
     * additional boolean cast
     * 
     * @param array $data
     * @param boolean $skipUpload skips autoupload
     * @param boolean $skipPasswordEncrypt skips password encrypting
     * @return Core_Model_ValueObject_Client
     */
    public function setData(array $data, $skipUpload = false, $skipPasswordEncrypt = false)
    {
        if (key_exists('password', $data) && !$skipPasswordEncrypt) {
            $data['password'] = md5($data['password']);
        }
        
        if (key_exists('avatar', $data) && !empty($data['avatar']) && !$skipUpload) {
            $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }

        parent::setData($data);
        
        // build label if there were changes
        if (key_exists('company', $data) || key_exists('prename', $data) ||
                key_exists('surname', $data)) {
            if($this->getData('company')) {
                $label = $this->getData('company');
            } else {
                $label = $this->getData('surname') . ' ' . $this->getData('prename');
            }
            
            $this->getBean()->setProperty('label', $label);
        }
        
        if (key_exists('company', $data) || key_exists('prename', $data) ||
                key_exists('surname', $data) || key_exists('status', $data) || 
                key_exists('avatar', $data)) {
            $this->_rebuildSearchIndex = true;
        }
        
        return $this;
    }
    
    /**
     * removes client avatar and deletes the existing file
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
     * removes a certain service in data backend
     * 
     * @param string $service name of the service
     * @return boolean
     */
    public function removeService($service)
    {
        if(!$this->hasService($service)) {
            return false;
        }

        if (count($this->getData("services")) == 1){
            return $this->unsetProperty("services")->save();
        }else {
            return $this->unsetProperty("services/$service")->save();
        }
    }
    
    /**
     * upload a client avatar
     * 
     * @param string $filename file to receive
     * @return string|null
     */
    public function uploadAvatar($filename)
    {
        $fileManager = Core_Model_DiFactory::newFileManager();
        $destination = APPLICATION_PATH . "/.." . self::UPLOADS_DIR;
        $formatImage = "%s.%s";
        $upload = false;
        
        if (!is_dir($destination) && !$fileManager->createFolder($destination, true)) {
            return null;
        }

        if (preg_match("/.+base64\,/", $filename) !== 0) {
            $upload = sprintf($formatImage, $this->getId(), "gif");
            $fileManager->base64DecodeToFile($destination . $upload, $filename);
        }else if ($fileManager->receiveHttpFileInfo($filename)){
            $upload = $fileManager->uploadFile($filename, $destination
                    , sprintf($formatImage, $this->getId()
                    , pathinfo($filename, PATHINFO_EXTENSION)));
        }
        
        if ($upload && file_exists($destination. $upload)){
            $fileManager->imageResize($destination. $upload, 200);
            $renamed = sprintf($formatImage, pathinfo($upload, PATHINFO_FILENAME), "gif");

            rename($destination. $upload, $destination. $renamed);
            if ($this->getData("avatar") != $renamed) {
                $this->removeAvatar();
            }

            return $renamed;
        }

        Core_Model_DiFactory::getMessageManager()
                ->addError(self::ERROR_UPLOAD_FAILED, $filename);

        return null;
    }
    
    /**
     * unsets a certain property
     * 
     * @param string $propertyPath
     * @return Core_Model_ValueObject_Client
     */
    public function unsetProperty($propertyPath) {
        parent::unsetProperty($propertyPath);
        
        if(str_replace('/', '', $propertyPath) == 'company') {
            if($this->getData('company')) {
                $label = $this->getData('company');
            } else {
                $label = $this->getData('surname') . ' ' . $this->getData('prename');
            }

            $this->getBean()->setProperty('label', $label);
        }
        
        return $this;
    }
    
}
