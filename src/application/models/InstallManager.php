<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_InstallManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_InstallManager
{

    /**
     * @var Zend_Config
     */
    protected $_config;

    /**
     * @var Zend_Session_Namespace
     */
    protected $_configSession;

    /**
     * message when admin user allready exists in selected database
     */
    CONST ADMIN_EXIST_IN_DB = 'Admin already exists in the database!';
    
    /**
     * file name of config
     */
    CONST CONFIG_FILE = 'server.ini';
    
    /**
     * message when config file couldnt be found
     */
    CONST CONFIG_NOT_FOUND = 'No configuration found! Please restart the installation';
    
    /**
     * path to config
     */
    CONST CONFIG_PATH = '/../data/configs/';
    
    /**
     * message when creating the admin user failed
     */
    CONST CREATE_ADMIN_FAILED = 'could not create admin user';
    
    /**
     * message when configuration could not be deployed
     */
    CONST DEPLOYMENT_FAILED = 'Configuration could not be deployed';
    
    /**
     * message when a maze installation was detected in current database
     */
    CONST MAZE_ALREADY_ON_DATABASE = "The installation wizard detected that already a Maze.dashboard installation has been performed on the given database";
    
    /**
     * message when write permissions are missing
     */
    CONST NO_PERMISSIONS = 'no write permissions in %1$s';
    
    /**
     * session name for installer
     */
    CONST SESSION_NAMESPACE = 'installer';
    
    /**
     * inits config session
     */
    public function __construct()
    {
        $configSession = $this->_getConfigSession();

        if ($configSession->config instanceof Zend_Config) {
            $this->setConfig($configSession->config);

            if ($configSession->config->get("language", false)) {
                $this->_setLanguage($configSession->config->get("language"));
            }
        }
    }
    
    /**
     * adds the content of the given array to the config object for further usage
     * 
     * @param array $data
     */
    protected function _addDataToConfig($data)
    {
        foreach ($data as $key => $value) {
            $this->getConfig()->$key = $value;
        }
    }
    
    /**
     * creates config.ini in file system and deploys new server config in application
     * 
     * @param  Zend_Config $config
     * @return boolean
     */
    protected function _buildAndDeployConfig($config = null)
    {
        if(!is_writable(APPLICATION_PATH . self::CONFIG_PATH)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::NO_PERMISSIONS, APPLICATION_PATH . self::CONFIG_PATH);
            return false;
        }
        
        if ($config == null) {
            $config = $this->_getServerConfig();
        }

        $configWriter = new Zend_Config_Writer_Ini();
        $configWriter->setConfig($config);
        $configWriter->write(APPLICATION_PATH . self::CONFIG_PATH . self::CONFIG_FILE);
        
        // change config registry in order to use new mongodb config
        Zend_Registry::getInstance()->get('config')->merge($config);
        Core_Model_DiFactory::reset();
        Core_Model_Dataprovider_DiFactory::reset();
        
        return true;
    }
    
    /**
     * creates the adminuser for login
     * 
     * @return boolean
     */
    protected function _createAdminUser()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        $config = $this->getConfig();

        $userData = array(
            'username' => $config->username,
            'password' => $config->password,
            'email' => $config->email,
            'status' => true
        );
        
        return $adminManager->createAdmin($userData);
    }
    
    /**
     * returns the existing config object from the installer session
     * 
     * @return Zend_Session_Namespace
     */
    protected function _getConfigSession()
    {
        if (!$this->_configSession instanceof Zend_Session_Namespace) {
            $this->_configSession = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        }

        return $this->_configSession;
    }
    
    /**
     * returns a mapped Zend_Config Object of the server config
     * 
     * @return Zend_Config
     */
    protected function _getServerConfig()
    {
        $serverConfigData = array(
            'mongodb' => array(
                'database' => $this->getConfig()->database,
                'collectionPrefix' => $this->getConfig()->collectionPrefix,
                'username' => $this->getConfig()->dbUsername,
                'password' => $this->getConfig()->dbPassword,
                'host' => $this->getConfig()->host,
                'port' => $this->getConfig()->port
            ),
            'security' => array(
                'hash' => $this->getConfig()->securekey
            )
        );
        
        return new Zend_Config($serverConfigData);
    }
    
    /**
     * saves language and company to the database
     * 
     * @return boolean
     */
    protected function _insertConfigIntoDb()
    {
        $config = Core_Model_DiFactory::getConfig();
        
        $data = array(
            "locale"  => $this->getConfig()->language,
            "company" => $this->getConfig()->company
        );

        return $config->setData($data)->save();
    }

    /**
     * sets given Zend_Config object into the session
     * 
     * @param Zend_Config $config
     */
    protected function _setConfigSession(Zend_Config $config)
    {
        $this->_getConfigSession()->config = $config;
    }

    /**
     * sets the given language for installation
     * 
     * @param string $locale
     */
    protected function _setLanguage($locale)
    {
        $registry = Zend_Registry::getInstance();

        if ($registry->isRegistered("Zend_Translate")) {
            $registry->get("Zend_Translate")->getAdapter()->setLocale($locale);
        }
    }

    /**
     * returns the existing config object
     * 
     * @return Zend_Config
     */
    public function getConfig()
    {
        if (!$this->_config instanceof Zend_Config) {
            $this->_config = new Zend_Config(array(), true);
        }

        return $this->_config;
    }
    
    /**
     * completes the installer process
     * -> creates the admin user
     * -> deploys server configuration
     * 
     * @return boolean
     */
    public function install()
    {
        $config = $this->getConfig()->toArray();
        if(empty($config)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CONFIG_NOT_FOUND);
            return false;
        }

        if(!$this->_buildAndDeployConfig()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DEPLOYMENT_FAILED);
            return false;
        }

        if(!$this->_createAdminUser()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CREATE_ADMIN_FAILED);
            return false;
        }

        if(!$this->_insertConfigIntoDb()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DEPLOYMENT_FAILED);
            return false;
        }

        if($this->isInstalled()) {
            Zend_Session::namespaceUnset(self::SESSION_NAMESPACE);
            return true;
        }
        
        return false;
    }

    /**
     * returns db connection status
     *
     * @return boolean
     */
    public function isDbConnection()
    {
        Core_Model_DiFactory::reset();

        return Core_Model_Dataprovider_DiFactory::getConnection()->status($this->_getServerConfig());
    }

    /**
     * checks that mmaze was correctly installed
     * -> config.ini
     * -> admin user
     * 
     * @return boolean
     */
    public function isInstalled()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        
        if (file_exists(APPLICATION_PATH . self::CONFIG_PATH . self::CONFIG_FILE) &&
                Core_Model_Dataprovider_DiFactory::getConnection()->status() && count($adminManager->getAdmins()) > 0) {
            return true;
        }

        return false;
    }

    /**
     * creates a new security key for application config
     */
    public function registerSecurekey()
    {
        $cryptManager = Core_Model_DiFactory::getCryptManager();

        $this->getConfig()->securekey = $cryptManager->generateKey();
        $this->_setConfigSession($this->getConfig());
    }

    /**
     * completes the installer process
     * -> copy exists admin user to the new database
     * -> deploys server configuration
     * 
     * @return boolean
     */
    public function reinstall()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $config = $this->getConfig()->toArray();
        if (Zend_Registry::getInstance()->get('config')->mongodb){
            $mongodb = Zend_Registry::getInstance()->get('config')->mongodb->toArray();
            $oldConf = new Zend_Config(array("mongodb" => $mongodb));
        }
        
        if(empty($config)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CONFIG_NOT_FOUND);
            return false;
        }

        if(!$this->_buildAndDeployConfig()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DEPLOYMENT_FAILED);
            return false;
        }

        if (Core_Model_DiFactory::getUserManager()->getUserByName($identity["username"])){
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MAZE_ALREADY_ON_DATABASE);
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::ADMIN_EXIST_IN_DB);
            
            if (isset($oldConf)) {
                    $this->_buildAndDeployConfig($oldConf);
            }

            return false;
        }

        unset($identity["_id"]);
        if(!Core_Model_Dataprovider_DiFactory::newAdmin()->saveAdmin($identity)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::CREATE_ADMIN_FAILED);
            return false;
        }

        if(!$this->_insertConfigIntoDb()) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::DEPLOYMENT_FAILED);
            return false;
        }

        if($this->isInstalled()) {
            Zend_Session::namespaceUnset(self::SESSION_NAMESPACE);
            return true;
        }
        
        return false;
    }

    /**
     * overwrites existing config with given Zend_Config object
     * 
     * @param Zend_Config $config
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * validates given Zend_Form and deploys config data to session and internal
     * config object
     * 
     * @param Zend_Form $form
     * @return boolean
     */
    public function validateAndAddToConfig(Zend_Form $form)
    {
        if (!$form->isValid($form->getValues())) {
            return false;
        }

        $this->_addDataToConfig($form->getValues());
        $this->_setConfigSession($this->getConfig());

        return true;
    }

}

