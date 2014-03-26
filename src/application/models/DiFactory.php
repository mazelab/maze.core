<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_DiFactory
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_DiFactory
{

    /**
     * @var array contains Core_Model_ValueObject_Admin
     */
    static protected $_admin;
    
    /**
     * @var Core_Model_AdminManager
     */
    static protected $_adminManager;
    
    /**
     * @var Core_Model_ApiManager
     */
    static protected $_apiManager;

    /**
     * @var array contains Core_Model_ValueObject_Client
     */
    static protected $_client;
    
    /**
     * @var Core_Model_ClientManager
     */
    static protected $_clientManager;
    
    /**
     * @var Core_Model_ValueObject_Config
     */
    static protected $_config;

    /**
     * @var Core_Model_ValueObject_Domain
     */
    static protected $_domain;
    
    /**
     * @var Core_Model_DomainManager
     */
    static protected $_domainManager;
    
    /**
     * @var Core_Model_EmailManager
     */
    static protected $_emailManager;
    
    /**
     * @var Core_Model_FileManager 
     */
    static protected $_fileManager;
    
    /**
     * @var Core_Model_IndexManager
     */
    static protected $_indexManager;
    
    /**
     * @var Core_Model_InstallManager
     */
    static protected $_installManager;
    
    /**
     * @var Core_Model_Logger
     */
    static protected $_logger;
    
    /**
     * @var Core_Model_LogManager
     */
    static protected $_logManager;
    
    /**
     * mapping admin email to admin ids
     * 
     * @var array
     */
    static protected $_mapAdminEmail;

    /**
     * mapping admin user names to admin ids
     * 
     * @var array
     */
    static protected $_mapAdminUsername;
    
    /**
     * mapping client email to admin ids
     * 
     * @var array
     */
    static protected $_mapClientEmail;

    /**
     * mapping client label to client ids
     * 
     * @var array
     */
    static protected $_mapClientLabel;
    
    /**
     * mapping client user names to client ids
     * 
     * @var array
     */
    static protected $_mapClientUsername;
    
    /**
     * mapping domain names to domain ids
     *
     * @var array
     */
    static protected $_mapDomainName;
    
    /**
     * mapping api key to node
     * 
     * @var array
     */
    static protected $_mapNodeApiKey;
    
    /**
     * mapping node name to node
     * 
     * @var array
     */
    static protected $_mapNodeName;
    
    /**
     * @var MongoDb_Mongo
     */
    static protected $_mongoDb;
    
    /**
     * @var Core_Model_ValueObject_Node
     */
    static protected $_node;
    
    /**
     * @var Core_Model_NodeManager
     */
    static protected $_nodeManager;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchAdmins;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchAll;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchClients;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchDomains;
    
    /**
     * @var Core_Model_Search_Index
     */
    static protected $_searchIndex;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchManager;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchNews;
    
    /**
     * @var Core_Model_SearchManager
     */
    static protected $_searchNodes;
    
    /**
     * @var Core_Model_ValueObject_Module 
     */
    static protected $_module;
    
    /**
     * @var Core_Model_Module_Composer 
     */
    static protected $_moduleComposer;
    
    /**
     * @var Core_Model_Module_Listings
     */
    static protected $_moduleListings;
    
    /**
     * @var Core_Model_ModuleManager
     */
    static protected $_moduleManager;
    
    /**
     * @var Core_Model_Module_Sync
     */
    static protected $_moduleSync;
    
    /**
     * @var Core_Model_NewsManager
     */
    static protected $_newsManager;
    
    /**
     * @var Core_Model_UserManager
     */
    static protected $_userManager;
    
    /**
     * @var Core_Model_CryptManager
     */
    static protected $_cryptManager;
    
    /**
     * returns certain instance of Core_Model_ValueObject_Admin
     * 
     * @param string $adminId
     * @return Core_Model_ValueObject_Admin|null
     */
    static public function getAdmin($adminId)
    {
        if(!isset(self::$_admin[$adminId])) {
            return null;
        }

        return self::$_admin[$adminId];
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Admin by email
     * 
     * uses _mapAdminEmail
     * 
     * @param string $domainName
     * @return Core_Model_ValueObject_Admin|null
     */
    static public function getAdminByEmail($email)
    {
        if(isset(self::$_mapAdminEmail[$email])) {
            return self::getAdmin(self::$_mapAdminEmail[$email]);
        }
        
        return null;
    }
    
   /**
     * returns certain instance of Core_Model_ValueObject_Admin by username
     * 
     * uses _mapAdminUsername
     * 
     * @param string $username
     * @return Core_Model_ValueObject_Admin|null
     */
    static public function getAdminByUserName($username)
    {
        if(isset(self::$_mapAdminUsername[$username])) {
            return self::getAdmin(self::$_mapAdminUsername[$username]);
        }
        
        return null;
    }
    
    /**
     * @return Core_Model_AdminManager
     */
    static public function getAdminManager()
    {
        if (!self::$_adminManager instanceof Core_Model_AdminManager) {
            self::$_adminManager = self::newAdminManager();
        }

        return self::$_adminManager;
    }
    
    /**
     * @return Core_Model_ApiManager
     */
    static public function getApiManager()
    {
        if (!self::$_apiManager instanceof Core_Model_ApiManager) {
            self::$_apiManager = self::newApiManager();
        }

        return self::$_apiManager;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Client
     * 
     * @param string $clientId
     * @return Core_Model_ValueObject_Client|null
     */
    static public function getClient($clientId)
    {
        if(!isset(self::$_client[$clientId])) {
            return null;
        }

        return self::$_client[$clientId];
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Client by email
     * 
     * uses _mapClientEmail
     * 
     * @param string $email
     * @return Core_Model_ValueObject_Client|null
     */
    static public function getClientByEmail($email)
    {
        if(isset(self::$_mapClientEmail[$email])) {
            return self::getClient(self::$_mapClientEmail[$email]);
        }
        
        return null;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Client by label
     * 
     * uses _mapClientLabel
     * 
     * @param string $label
     * @return Core_Model_ValueObject_Client|null
     */
    static public function getClientByLabel($label)
    {
        if(isset(self::$_mapClientLabel[$label])) {
            return self::getClient(self::$_mapClientLabel[$label]);
        }
        
        return null;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Client by username
     * 
     * uses _mapClientUsername
     * 
     * @param string $username
     * @return Core_Model_ValueObject_Client|null
     */
    static public function getClientByUserName($username)
    {
        if(isset(self::$_mapClientUsername[$username])) {
            return self::getClient(self::$_mapClientUsername[$username]);
        }
        
        return null;
    }
    
    /**
     * @return Core_Model_ClientManager
     */
    static public function getClientManager()
    {
        if (!self::$_clientManager instanceof Core_Model_ClientManager) {
            self::$_clientManager = self::newClientManager();
        }

        return self::$_clientManager;
    }
    
    /**
     * returns certain instance of Core_Model_Config
     * 
     * @return Core_Model_ValueObject_Config
     */
    static public function getConfig()
    {
        if (!self::$_config instanceof Core_Model_ValueObject_Config) {
            self::$_config = self::newConfig();
        }

        return self::$_config;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Domain
     * 
     * @param string $domainId
     * @return Core_Model_ValueObject_Domain
     */
    static public function getDomain($domainId)
    {
        if(!isset(self::$_domain[$domainId])) {
            return null;
        }

        return self::$_domain[$domainId];
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Domain by email
     * 
     * uses _mapDomainName
     * 
     * @param string $domainName
     * @return Core_Model_ValueObject_Domain|null
     */
    static public function getDomainByName($domainName)
    {
        if(isset(self::$_mapDomainName[$domainName])) {
            return self::getDomain(self::$_mapDomainName[$domainName]);
        }
        
        return null;
    }
    
    /**
     * @return Core_Model_DomainManager
     */
    static public function getDomainManager()
    {
        if (!self::$_domainManager instanceof Core_Model_DomainManager) {
            self::$_domainManager = self::newDomainManager();
        }

        return self::$_domainManager;
    }
    
    /**
     * @return Core_Model_EmailManager
     */
    static public function getEmailManager()
    {
        if (!self::$_emailManager instanceof Core_Model_EmailManager) {
            self::$_emailManager = self::newEmailManager();
        }

        return self::$_emailManager;
    }
    
    /**
     * @return Core_Model_FileManager
     */
    static public function getFileManager()
    {
        if (!self::$_fileManager instanceof Core_Model_FileManager) {
            self::$_fileManager = self::newFileManager();
        }

        return self::$_fileManager;
    }
    
    /**
     * @return Core_Model_LogManager
     */
    static public function getLogManager()
    {
        if (!self::$_logManager instanceof Core_Model_LogManager) {
            self::$_logManager = self::newLogManager();
        }

        return self::$_logManager;
    }
    
    /**
     * Application logging machanism
     * 
     * @return Core_Model_Logger
     */
    static public function getLogger()
    {
        if (!self::$_logger instanceof Core_Model_Logger) {
            self::$_logger = self::newLogger();
        }

        return self::$_logger;
    }

    /**
     * @return Core_Model_IndexManager
     */
    static public function getIndexManager()
    {
        if(!self::$_indexManager instanceof Core_Model_IndexManager) {
            self::$_indexManager = self::newIndexManager();
        }

        return self::$_indexManager;
    }
    
    /**
     * @return Core_Model_InstallManager
     */
    static public function getInstallManager()
    {
        // if object doesnt exists create a new one
        if(!self::$_installManager instanceof Core_Model_InstallManager) {
            self::$_installManager = self::newInstallManager();
        }

        return self::$_installManager;
    }
    
    /**
     * returns message manager instance
     * 
     * @return Core_Model_MessageManager
     */
    static public function getMessageManager()
    {
        return Core_Model_MessageManager::getInstance();
    }
    
    /**
     * returns isntance of mongodb
     *
     * @param  Zend_Config $config
     * @return MongoDb_Mongo
     */
    static public function getMongoDb(Zend_Config $config = null)
    {
        if (!self::$_mongoDb instanceof MongoDb_Mongo) {
            self::$_mongoDb = self::newMongoDb($config);
        }

        return self::$_mongoDb;
    }
    
    /**
     * @return Core_Model_NewsManager
     */
    static public function getNewsManager()
    {
        if (!self::$_newsManager instanceof Core_Model_NewsManager) {
            self::$_newsManager = self::newNewsManager();
        }

        return self::$_newsManager;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Node
     * 
     * @param string $nodeId
     * @return Core_Model_ValueObject_Node|null
     */
    static public function getNode($nodeId)
    {
        if(!isset(self::$_node[$nodeId])) {
            return null;
        }

        return self::$_node[$nodeId];
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Node by api key
     * 
     * uses _mapNodeApiKey
     * 
     * @param string $apiKey
     * @return Core_Model_ValueObject_Node|null
     */
    static public function getNodeByApiKey($apiKey)
    {
        if(isset(self::$_mapNodeApiKey[$apiKey])) {
            return self::getNode(self::$_mapNodeApiKey[$apiKey]);
        }
        
        return null;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Node by name
     * 
     * uses _mapNodeName
     * 
     * @param string $nodeName
     * @return Core_Model_ValueObject_Node|null
     */
    static public function getNodeByName($nodeName)
    {
        if(isset(self::$_mapNodeName[$nodeName])) {
            return self::getNode(self::$_mapNodeName[$nodeName]);
        }
        
        return null;
    }
    
    /**
     * returns instance of node manager
     * 
     * @return Core_Model_NodeManager
     */
    static public function getNodeManager()
    {
        if (!self::$_nodeManager instanceof Core_Model_NodeManager) {
            self::$_nodeManager = self::newNodeManager();
        }

        return self::$_nodeManager;
    }
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchAdministrators()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchAdmins()
    {
        if (!self::$_searchAdmins instanceof Core_Model_SearchManager) {
            self::$_searchAdmins = self::newSearchAdmins();
        }

        return self::$_searchAdmins;
    }
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchAll()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchAll()
    {
        if (!self::$_searchAll instanceof Core_Model_SearchManager) {
            self::$_searchAll = self::newSearchAll();
        }

        return self::$_searchAll;
    }
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchClients()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchClients()
    {
        if (!self::$_searchClients instanceof Core_Model_SearchManager) {
            self::$_searchClients = self::newSearchClients();
        }

        return self::$_searchClients;
    }
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchDomains()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchDomains()
    {
        if (!self::$_searchDomains instanceof Core_Model_SearchManager) {
            self::$_searchDomains = self::newSearchDomains();
        }

        return self::$_searchDomains;
    }
    
    /**
     * returns instance of Core_Model_Search_Index with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchIndex()
     * 
     * @return Core_Model_Search_Index
     */
    static public function getSearchIndex()
    {
        if (!self::$_searchIndex instanceof Core_Model_Search_Index) {
            self::$_searchIndex = self::newSearchIndex();
        }

        return self::$_searchIndex;
    }    
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchNews()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchNews()
    {
        if (!self::$_searchNews instanceof Core_Model_SearchManager) {
            self::$_searchNews = self::newSearchNews();
        }

        return self::$_searchNews;
    }
    
    /**
     * returns instance of Core_Model_SearchManager with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchNodes()
     * 
     * @return Core_Model_SearchManager
     */
    static public function getSearchNodes()
    {
        if (!self::$_searchNodes instanceof Core_Model_SearchManager) {
            self::$_searchNodes = self::newSearchNodes();
        }

        return self::$_searchNodes;
    }
    
    /**
     * returns certain instance of Core_Model_ValueObject_Module
     * 
     * @param string $moduleName
     * @return Core_Model_ValueObject_Module|null
     */
    static public function getModule($moduleName)
    {
        if(!isset(self::$_module[$moduleName])) {
            return null;
        }

        return self::$_module[$moduleName];
    }
    
    /**
     * @return Core_Model_Module_Api
     */
    static public function getModuleApi()
    {
        return Core_Model_Module_Api::getInstance();
    }

    /**
     * @return Core_Model_Module_Listings
     */
    static public function getModuleListings()
    {
        if (!self::$_moduleListings instanceof Core_Model_Module_Listings) {
            self::$_moduleListings = self::newModuleListings();
        }

        return self::$_moduleListings;
    }
    
    /**
     * @return Core_Model_ModuleManager
     */
    static public function getModuleManager()
    {
        if (!self::$_moduleManager instanceof Core_Model_ModuleManager) {
            self::$_moduleManager = self::newModuleManager();
        }

        return self::$_moduleManager;
    }
    
    /**
     * return current Instance of Core_Model_Module_Registry
     * 
     * @return Core_Model_Module_Registry
     */
    static public function getModuleRegistry()
    {
        return Core_Model_Module_Registry::getInstance();
    }
    
    /**
     * @return Core_Model_Module_Sync
     */
    static public function getModuleSync()
    {
        if (!self::$_moduleSync instanceof Core_Model_Module_Sync) {
            self::$_moduleSync = self::newModuleSync();
        }

        return self::$_moduleSync;
    }
    
    /**
     * @return Core_Model_UserManager
     */
    static public function getUserManager()
    {
        if (!self::$_userManager instanceof Core_Model_UserManager) {
            self::$_userManager = self::newUserManager();
        }

        return self::$_userManager;
    }

    /**
     * @return Core_Model_CryptManager
     */
    static public function getCryptManager()
    {
        if (!self::$_cryptManager instanceof Core_Model_CryptManager) {
            self::$_cryptManager = self::newCryptManager();
        }

        return self::$_cryptManager;
    }
    
    /**
     * checks if a certain admin instance is allready registered
     * 
     * @param string $adminId
     * @return boolean
     */
    static public function isAdminRegistered($adminId)
    {
        if(!isset(self::$_admin[$adminId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain client instance is allready registered
     * 
     * @param string $clientId
     * @return boolean
     */
    static public function isClientRegistered($clientId)
    {
        if(!isset(self::$_client[$clientId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain domain instance is allready registered
     * 
     * @param string $domainId
     * @return boolean
     */
    static public function isDomainRegistered($domainId)
    {
        if(!isset(self::$_domain[$domainId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain node instance is allready registered
     * 
     * @param string $nodeId
     * @return boolean
     */
    static public function isNodeRegistered($nodeId)
    {
        if(!isset(self::$_node[$nodeId])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * checks if a certain module instance is allready registered
     * 
     * @param string $moduleName
     * @return boolean
     */
    static public function isModuleRegistered($moduleName)
    {
        if(!isset(self::$_module[$moduleName])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return Core_Model_ValueObject_Admin
     */
    static public function newAdmin($adminId = null)
    {
        return new Core_Model_ValueObject_Admin($adminId);
    }
    
    /**
     * returns new instance of Core_Model_AdminManager
     * 
     * @return Core_Model_AdminManager
     */
    static public function newAdminManager()
    {
        return new Core_Model_AdminManager();
    }
    
    /**
     * returns new instance of Core_Model_ApiManager
     * 
     * @return Core_Model_ApiManager
     */
    static public function newApiManager()
    {
        return new Core_Model_ApiManager();
    }
    
    /**
     * @return Core_Model_ValueObject_Client
     */
    static public function newClient($clientId = null)
    {
        return new Core_Model_ValueObject_Client($clientId);
    }
    
    /**
     * returns new instance of Core_Model_ClientManager
     * 
     * @return Core_Model_ClientManager
     */
    static public function newClientManager()
    {
        return new Core_Model_ClientManager();
    }
    
    /**
     * returns new Core_Model_Config instance
     * 
     * @param string $configId
     * @return Core_Model_ValueObject_Config
     */
    static public function newConfig()
    {
        return new Core_Model_ValueObject_Config();
    }

    /**
     * returns new Core_Model_Domain instance
     * 
     * @param string $domainId
     * @return Core_Model_ValueObject_Domain
     */
    static public function newDomain($domainId = null)
    {
        return new Core_Model_ValueObject_Domain($domainId);
    }
    
    /**
     * returns new instance of Core_Model_DomainManager
     * 
     * @return Core_Model_DomainManager
     */
    static public function newDomainManager()
    {
        return new Core_Model_DomainManager();
    }
    
    /**
     * @return Core_Model_EmailManager
     */
    static public function newEmailManager()
    {
        return new Core_Model_EmailManager();
    }
    
    /**
     * @return Core_Model_FileManager
     */
    static public function newFileManager()
    {
        return new Core_Model_FileManager();
    }
    
    /**
     * returns new instance of Core_Model_LogManager
     * 
     * @return Core_Model_LogManager
     */
    static public function newLogManager()
    {
        return new Core_Model_LogManager();
    }
        
    /**
     * @return Core_Model_Logger
     */
    static public function newLogger()
    {
        return new Core_Model_Logger();
    }
    
    /**
     * creates instance of Core_Model_IndexManager
     * 
     * @return Core_Model_IndexManager
     */
    static public function newIndexManager()
    {
        return new Core_Model_IndexManager();
    }
    
    /**
     * creates instance of Core_Model_InstallManager
     * 
     * @return Core_Model_InstallManager
     */
    static public function newInstallManager()
    {
        return new Core_Model_InstallManager();
    }
    
    /**
     * @param Zend_Config $config
     * @param boolean $catchExceptions
     * @return null|MongoDb_Mongo 
     */
    static public function newMongoDb(Zend_Config $config = null, $catchExceptions = false)
    {
        if ($catchExceptions) {
            try {
                return new MongoDb_Mongo($config);
            } catch (Exception $e) {
                return null;
            }
        }
        
        return new MongoDb_Mongo($config);
    }
    
    /**
     * @return Core_Model_NewsManager
     */
    static public function newNewsManager()
    {
        return new Core_Model_NewsManager();
    }
    
    /**
     * returns new Core_Model_Node instance
     * 
     * @param string $nodeId
     * @return Core_Model_ValueObject_Node
     */
    static public function newNode($nodeId = null)
    {
        return new Core_Model_ValueObject_Node($nodeId);
    }
    
    /**
     * returns new instance of Core_Model_NodeManager
     * 
     * @return Core_Model_NodeManager
     */
    static public function newNodeManager()
    {
        return new Core_Model_NodeManager();
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::newSearchAdmins()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchAdmins()
    {
        $searchAdmins = new Core_Model_SearchManager();
        $searchAdmins->setProvider(Core_Model_Dataprovider_DiFactory::getSearchAdmins());
        
        return $searchAdmins;
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::getSearchAll()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchAll()
    {
        $searchAll = new Core_Model_SearchManager();
        $searchAll->setProvider(Core_Model_Dataprovider_DiFactory::getSearchAll());
        
        return $searchAll;
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::getSearchClients()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchClients()
    {
        $searchClients = new Core_Model_SearchManager();
        $searchClients->setProvider(Core_Model_Dataprovider_DiFactory::getSearchClients());
        
        return $searchClients;
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::getSearchDomains()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchDomains()
    {
        $searchDomains = new Core_Model_SearchManager();
        $searchDomains->setProvider(Core_Model_Dataprovider_DiFactory::getSearchDomains());
        
        return $searchDomains;
    }
    
    /**
     * returns new instance of Core_Model_Search_Index with dataprovider 
     * Core_Model_Dataprovider_DiFactory::getSearchIndex()
     * 
     * @return Core_Model_Search_Index
     */
    static public function newSearchIndex()
    {
        return new Core_Model_Search_Index();
    }
    
    /**
     * returns new instance of Core_Model_Search
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchManager()
    {
        return new Core_Model_SearchManager();
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::newSearchNews()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchNews()
    {
        $searchNews = new Core_Model_SearchManager();
        $searchNews->setProvider(Core_Model_Dataprovider_DiFactory::getSearchNews());

        return $searchNews;
    }
    
    /**
     * returns new instance of Core_Model_SearchManager with the dataprovider
     * Core_Model_DataProvider_DiFactory::getSearchNodes()
     * 
     * @return Core_Model_SearchManager
     */
    static public function newSearchNodes()
    {
        $searchNodes = new Core_Model_SearchManager();
        $searchNodes->setProvider(Core_Model_Dataprovider_DiFactory::getSearchNodes());
        
        return $searchNodes;
    }
    
    /**
     * @param Core_Model_NewsManager $manager 
     */
    static public function setNewsManager(Core_Model_NewsManager $manager = null)
    {
        self::$_newsManager = $manager;
    }
    
    /**
     * returns new Core_Model_ValueObject_Module instance
     * 
     * @param string $moduleName name of the module
     * @return Core_Model_ValueObject_Module
     */
    static public function newModule($moduleName = null)
    {
        return new Core_Model_ValueObject_Module($moduleName);
    }

    /**
     * returns new instance of Core_Model_Module_Composer
     * 
     * @return Core_Model_Module_Composer
     */
    static public function newModuleComposer($path = null)
    {
        return new Core_Model_Module_Composer($path);
    }
    
    /**
     * returns new instance of Core_Model_Module_Listings
     * 
     * @return Core_Model_Module_Listings
     */
    static public function newModuleListings()
    {
        return new Core_Model_Module_Listings();
    }
    
    /**
     * returns new instance of Core_Model_ModuleManager
     * 
     * @return Core_Model_ModuleManager
     */
    static public function newModuleManager()
    {
        return new Core_Model_ModuleManager();
    }
    
    /**
     * returns new instance of Core_Model_Module_Sync
     * 
     * @return Core_Model_Module_Sync
     */
    static public function newModuleSync()
    {
        return new Core_Model_Module_Sync();
    }
    
    /**
     * returns new instance of Core_Model_Node_Commands
     * 
     * @return Core_Model_Node_Commands
     */
    static public function newNodeCommand($nodeId = null)
    {
        return new Core_Model_Node_Commands($nodeId);
    }    
    
    /**
     * @return Core_Model_UserManager
     */
    static public function newUserManager()
    {
        return new Core_Model_UserManager();
    }
    
    /**
     * @return Core_Model_CryptManager
     */
    static public function newCryptManager()
    {
        return new Core_Model_CryptManager();
    }
    
    /**
     * registers a certain admin instance
     * 
     * @param string $adminId
     * @param Core_Model_ValueObject_Admin $admin
     */
    static public function registerAdmin($adminId, Core_Model_ValueObject_Admin $admin)
    {
        self::$_admin[$adminId] = $admin;
        self::$_mapAdminUsername[$admin->getUsername()] = $adminId;
        self::$_mapAdminEmail[$admin->getEmail()] = $adminId;
    }
    
    /**
     * registers a certain client instance
     * 
     * @param string $clientId
     * @param Core_Model_ValueObject_Client $client
     */
    static public function registerClient($clientId, Core_Model_ValueObject_Client $client)
    {
        self::$_client[$clientId] = $client;
        self::$_mapClientUsername[$client->getUsername()] = $clientId;
        self::$_mapClientEmail[$client->getEmail()] = $clientId;
        self::$_mapClientLabel[$client->getLabel()] = $clientId;
    }
    
    /**
     * registers a certain domain instance
     * 
     * @param string $domainId
     * @param Core_Model_ValueObject_Domain $domain
     */
    static public function registerDomain($domainId, Core_Model_ValueObject_Domain $domain)
    {
        self::$_domain[$domainId] = $domain;
        self::$_mapDomainName[$domain->getName()] = $domainId;
    }
    
    /**
     * registers a certain node instance
     * 
     * @param string $nodeId
     * @param Core_Model_ValueObject_Node $node
     */
    static public function registerNode($nodeId, Core_Model_ValueObject_Node $node)
    {
        self::$_node[$nodeId] = $node;
        
        if($node->getApiKey()) {
            self::$_mapNodeApiKey[$node->getApiKey()] = $nodeId;
        }
        if($node->getName()) {
            self::$_mapNodeName[$node->getName()] = $nodeId;
        }
    }
    
    /**
     * registers a certain module instance
     * 
     * @param string $moduleName name of the module
     * @param Core_Model_ValueObject_Module $module
     */
    static public function registerModule($moduleName, Core_Model_ValueObject_Module $module)
    {
        self::$_module[$moduleName] = $module;
    }
    
    /**
     * resets difactory instances
     */
    static public function reset()
    {
        self::setAdminManager();
        self::setApiManager();
        self::setClientManager();
        self::setConfig();
        self::setDomainManager();
        self::setLogManager();
        self::setLogger();
        self::setInstallManager();
        self::setMongoDb();
        self::setNodeManager();
        self::setSearchClients();
        self::setSearchDomains();
        self::setSearchIndex();
        self::setSearchManager();
        self::setSearchNodes();
        self::setModuleListings();
        self::setModuleManager();
        self::setModuleSync();
        self::setNewsManager();
        self::setSearchAll();
        self::setUserManager();
        self::setCryptManager();
        
        self::$_admin = null;
        self::$_client = null;
        self::$_domain = null;
        self::$_node = null;
        self::$_module = null;
    }

    /**
     * @param Core_Model_AdminManager $manager 
     */
    static public function setAdminManager(Core_Model_AdminManager $manager = null)
    {
        self::$_adminManager = $manager;
    }
    
    /**
     * @param Core_Model_ApiManager $manager 
     */
    static public function setApiManager(Core_Model_ApiManager $manager = null)
    {
        self::$_apiManager = $manager;
    }
    
    /**
     * @param Core_Model_ClientManager $manager 
     */
    static public function setClientManager(Core_Model_ClientManager $manager = null)
    {
        self::$_clientManager = $manager;
    }
    
    /**
     * sets certain instance of Core_Model_ValueObject_Config
     * 
     * @param Core_Model_ValueObject_Config $config
     */
    static public function setConfig(Core_Model_ValueObject_Config $config = null)
    {
        self::$_config = $config;
    }
    
    /**
     * @param Core_Model_DomainManager $manager 
     */
    static public function setDomainManager(Core_Model_DomainManager $manager = null)
    {
        self::$_domainManager = $manager;
    }
    
    /**
     * @param Core_Model_LogManager $manager 
     */
    static public function setLogManager(Core_Model_LogManager $manager = null)
    {
        self::$_logManager = $manager;
    }
    
    /**
     * @param Core_Model_Logger $logger 
     */
    static public function setLogger(Core_Model_Logger $logger = null)
    {
        self::$_logger = $logger;
    }
    
    /**
     * sets instance of Core_Model_InstallManager
     * 
     * @param Core_Model_InstallManager $manager
     */
    static public function setInstallManager(Core_Model_InstallManager $manager = null)
    {
        self::$_installManager = $manager;
    }
    
    /**
     * @param MongoDb_Mongo $manager 
     */
    static public function setMongoDb(MongoDb_Mongo $mongoDb = null)
    {
        self::$_mongoDb = $mongoDb;
    }
    
    /**
     * @param Core_Model_NodeManager $manager 
     */
    static public function setNodeManager(Core_Model_NodeManager $manager = null)
    {
        self::$_nodeManager = $manager;
    }
    
    /**
     * @param Core_Model_SearchManager $searchAll
     */
    static public function setSearchAll(Core_Model_SearchManager $searchAll = null)
    {
        self::$_searchAll = $searchAll;
    }
    
    /**
     * @param Core_Model_SearchManager $searchClients
     */
    static public function setSearchClients(Core_Model_SearchManager $searchClients = null)
    {
        self::$_searchClients = $searchClients;
    }
    
    /**
     * @param Core_Model_SearchManager $searchDomains
     */
    static public function setSearchDomains(Core_Model_SearchManager $searchDomains = null)
    {
        self::$_searchDomains = $searchDomains;
    }
    
    /**
     * @param Core_Model_Search_Index $searchIndex
     */
    static public function setSearchIndex(Core_Model_Search_Index $searchIndex = null)
    {
        self::$_searchIndex = $searchIndex;
    }
    
    /**
     * @param Core_Model_SearchManager $searchManager
     */
    static public function setSearchManager(Core_Model_SearchManager $searchManager = null)
    {
        self::$_searchManager = $searchManager;
    }
    
    /**
     * @param Core_Model_SearchManager $searchNodes
     */
    static public function setSearchNodes(Core_Model_SearchManager $searchNodes = null)
    {
        self::$_searchNodes = $searchNodes;
    }
    
    /**
     * @param Core_Model_Module_Listing $listing
     */
    static public function setModuleListings(Core_Model_Module_Listing $listing = null)
    {
        self::$_moduleListings = $listing;
    }
    
    /**
     * @param Core_Model_ModuleManager $manager 
     */
    static public function setModuleManager(Core_Model_ModuleManager $manager = null)
    {
        self::$_moduleManager = $manager;
    }
    
    /**
     * @param Core_Model_Module_Sync $sync
     */
    static public function setModuleSync(Core_Model_Module_Sync $sync = null)
    {
        self::$_moduleSync = $sync;
    }
    
    /**
     * @param Core_Model_UserManager $manager 
     */
    static public function setUserManager(Core_Model_UserManager $manager = null)
    {
        self::$_userManager = $manager;
    }
    
    /**
     * @param Core_Model_CryptManager $manager 
     */
    static public function setCryptManager(Core_Model_CryptManager $manager = null)
    {
        self::$_cryptManager = $manager;
    }
    
    /**
     * unregisters a certain admin instance
     * 
     * @param string $adminId
     */
    static public function unregisterAdmin($adminId)
    {
        if(isset(self::$_admin[$adminId]) && ($admin = self::getAdmin($adminId))) {
            unset(self::$_mapAdminEmail[$admin->getEmail()]);
            unset(self::$_mapAdminUsername[$admin->getUsername()]);
            unset(self::$_admin[$adminId]);
        }
    }
    
    /**
     * unregisters a certain client instance
     * 
     * @param string $clientId
     */
    static public function unregisterClient($clientId)
    {
        if(isset(self::$_client[$clientId]) && ($client = self::getClient($clientId))) {
            unset(self::$_mapClientEmail[$client->getEmail()]);
            unset(self::$_mapClientUsername[$client->getUsername()]);
            unset(self::$_mapClientLabel[$client->getLabel()]);
            unset(self::$_client[$clientId]);
        }
    }
    
    /**
     * unregisters a certain domain instance
     * 
     * @param string $domainId
     */
    static public function unregisterDomain($domainId)
    {
        if(isset(self::$_domain[$domainId]) && ($domain = self::getDomain($domainId))) {
            unset(self::$_mapDomainName[$domain->getName()]);
            unset(self::$_domain[$domainId]);
        }
    }
    
    /**
     * unregisters a certain node instance
     * 
     * @param string $nodeId
     */
    static public function unregisterNode($nodeId)
    {
        if(isset(self::$_node[$nodeId]) &&
                ($node =  self::getNode($nodeId))) {
            unset(self::$_mapNodeApiKey[$node->getApiKey()]);
            unset(self::$_mapNodeName[$node->getName()]);
            unset(self::$_node[$nodeId]);
        }
    }
    
    /**
     * unregisters a certain module instance
     * 
     * @param string $moduleName name of the module
     */
    static public function unregisterModule($moduleName)
    {
        if(isset(self::$_module[$moduleName])) {
            unset(self::$_module[$moduleName]);
        }
    }
    
}

