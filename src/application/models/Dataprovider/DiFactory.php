<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_DiFactory
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_DiFactory
{
    
    /**
     * admin instance
     * 
     * @var Core_Model_Dataprovider_Interface_Admin
     */
    static protected $_admin;
    
    /**
     * current adapter
     * 
     * @var string
     */
    static protected $_adapter;
    
    /**
     * client instance
     * 
     * @var Core_Model_Dataprovider_Interface_Client
     */
    static protected $_client;
    
    /**
     * config instance
     * 
     * @var Core_Model_Dataprovider_Interface_Config
     */
    static protected $_config;
    
    /**
     * connection instance
     * 
     * @var Core_Model_Dataprovider_Interface_Connection
     */
    static protected $_connection;
    
    /**
     * domain instance
     * 
     * @var Core_Model_Dataprovider_Interface_Domain
     */
    static protected $_domain;
    
    /**
     * log instance
     * 
     * @var Core_Model_Dataprovider_Interface_Log
     */
    static protected $_log;
    
    /**
     * module instance
     *
     * @var Core_Model_Dataprovider_Interface_Module
     */
    static protected $_module;
    
    /**
     * news instance
     * 
     * @var Core_Model_Dataprovider_Interface_News
     */
    static protected $_news;
    
    /**
     * node instance
     * 
     * @var Core_Model_Dataprovider_Interface_Node
     */
    static protected $_node;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchAdmins;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchAll;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchClients;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchDomains;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchIndex;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchNews;
    
    /**
     * search instance
     * 
     * @var Core_Model_Dataprovider_Interface_Search
     */
    static protected $_searchNodes;
    
    /**
     * user instance
     * 
     * @var Core_Model_Dataprovider_Interface_User
     */
    static protected $_user;
    
    /**
     * default adapter
     */
    CONST DEFAULT_ADAPTER = 'Core';
    
    /**
     * provider class prefix for class building
     */
    CONST PROVIDER_CLASS_PATH_PRE = 'Core_Model_Dataprovider_';
    
    /**
     * get current adapter
     * 
     * @return string
     */
    static public function getAdapter()
    {
        if (!self::$_adapter) {
            self::resetAdapter();
        }
        
        return self::$_adapter;
    }
    
    /**
     * get admin instance
     * 
     * @return Core_Model_Dataprovider_Interface_Admin
     */
    static public function getAdmin()
    {
        if (!self::$_admin instanceof Core_Model_Dataprovider_Interface_Admin) {
            self::$_admin = self::newAdmin();
        }

        return self::$_admin;
    }

    /**
     * get client instance
     * 
     * @return Core_Model_Dataprovider_Interface_Client
     */
    static public function getClient()
    {
        if (!self::$_client instanceof Core_Model_Dataprovider_Interface_Client) {
            self::$_client = self::newClient();
        }

        return self::$_client;
    }
    
    /**
     * get config instance
     * 
     * @return Core_Model_Dataprovider_Interface_Config
     */
    static public function getConfig()
    {
        if (!self::$_config instanceof Core_Model_Dataprovider_Interface_Config) {
            self::$_config = self::newConfig();
        }

        return self::$_config;
    }
    
    /**
     * get connection instance
     * 
     * @return Core_Model_Dataprovider_Interface_Connection
     */
    static public function getConnection()
    {
        if (!self::$_connection instanceof Core_Model_Dataprovider_Interface_Connection) {
            self::$_connection = self::newConnection();
        }

        return self::$_connection;
    }

    /**
     * get domain instance
     * 
     * @return Core_Model_Dataprovider_Interface_Domain
     */
    static public function getDomain()
    {
        if (!self::$_domain instanceof Core_Model_Dataprovider_Interface_Domain) {
            self::$_domain = self::newDomain();
        }

        return self::$_domain;
    }
    
    /**
     * get log instance
     * 
     * @return Core_Model_Dataprovider_Interface_Log
     */
    static public function getLog()
    {
        if (!self::$_log instanceof Core_Model_Dataprovider_Interface_Log) {
            self::$_log = self::newLog();
        }

        return self::$_log;
    }

    /**
     * get module instance
     * 
     * @return Core_Model_Dataprovider_Interface_Module
     */
    static public function getModule()
    {
        if (!self::$_module instanceof Core_Model_Dataprovider_Interface_Module) {
            self::$_module = self::newModule();
        }

        return self::$_module;
    }
    
    /**
     * get news instance
     * 
     * @return Core_Model_Dataprovider_Interface_News
     */
    static public function getNews()
    {
        if (!self::$_news instanceof Core_Model_Dataprovider_Interface_News) {
            self::$_news = self::newNews();
        }

        return self::$_news;
    }
    
    /**
     * get node instance
     * 
     * @return Core_Model_Dataprovider_Interface_Node
     */
    static public function getNode()
    {
        if (!self::$_node instanceof Core_Model_Dataprovider_Interface_Node) {
            self::$_node = self::newNode();
        }

        return self::$_node;
    }

    /**
     * get search admin instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchAdmins()
    {
        if (!self::$_searchAdmins instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchAdmins = self::newSearchAdmins();
        }

        return self::$_searchAdmins;
    }
    
    /**
     * get search all instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchAll()
    {
        if (!self::$_searchAll instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchAll = self::newSearchAll();
        }

        return self::$_searchAll;
    }
    
    /**
     * get search client instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchClients()
    {
        if (!self::$_searchClients instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchClients = self::newSearchClients();
        }

        return self::$_searchClients;
    }
    
    /**
     * get search domain instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchDomains()
    {
        if (!self::$_searchDomains instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchDomains = self::newSearchDomains();
        }

        return self::$_searchDomains;
    }
    
    /**
     * get search index instance
     * 
     * @return Core_Model_Dataprovider_Interface_SearchIndex
     */
    static public function getSearchIndex()
    {
        if (!self::$_searchIndex instanceof Core_Model_Dataprovider_Interface_SearchIndex) {
            self::$_searchIndex = self::newSearchIndex();
        }

        return self::$_searchIndex;
    }
    
    /**
     * get search news instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchNews()
    {
        if (!self::$_searchNews instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchNews = self::newSearchNews();
        }

        return self::$_searchNews;
    }
    
    /**
     * get search node instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     */
    static public function getSearchNodes()
    {
        if (!self::$_searchNodes instanceof Core_Model_Dataprovider_Interface_Search) {
            self::$_searchNodes = self::newSearchNodes();
        }

        return self::$_searchNodes;
    }
    
    /**
     * get user instance
     * 
     * @return Core_Model_Dataprovider_Interface_User
     */
    static public function getUser()
    {
        if (!self::$_user instanceof Core_Model_Dataprovider_Interface_User) {
            self::$_user = self::newUser();
        }

        return self::$_user;
    }
    
    /**
     * create new admin object
     * 
     * @return Core_Model_Dataprovider_Interface_Admin
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newAdmin()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Admin';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Admin) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid admin implementation.'
        );
    }
    
    /**
     * create new client object
     * 
     * @return Core_Model_Dataprovider_Interface_Client
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newClient()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Client';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Client) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid client implementation.'
        );
    }
    
    /**
     * create new config object
     * 
     * @return Core_Model_Dataprovider_Interface_Config
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newConfig()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Config';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Config) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid config implementation.'
        );
    }
    
    /**
     * create connection instance
     * 
     * @return Core_Model_Dataprovider_Interface_Connection
     * @throws Core_Model_Dataprovider_Exception
     */
    static public function newConnection()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Connection';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Connection) {
            return $newOne;
        }

        throw new Core_Model_Dataprovider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid connection implementation.'
        );
    }
    
    /**
     * create new domain object
     * 
     * @return Core_Model_Dataprovider_Interface_Domain
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newDomain()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Domain';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Domain) {
            return $newOne;
        }

        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid domain implementation.'
        );
    }
    
    /**
     * create new log instance
     * 
     * @return Core_Model_Dataprovider_Interface_Log
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newLog()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Log';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Log) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid log implementation.'
        );
    }
    
    /**
     * create new module object
     * 
     * @return Core_Model_Dataprovider_Interface_Module
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newModule()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Module';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Module) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid module implementation.'
        );
    }

    /**
     * create new news instance
     * 
     * @return Core_Model_Dataprovider_Interface_News
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newNews()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_News';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_News) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid user implementation.'
        );
    }
    
    /**
     * create new node object
     * 
     * @return Core_Model_Dataprovider_Interface_Node
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newNode()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Node';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Node) {
            return $newOne;
        }

        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid node implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchAdmins()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_Admins';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchAll()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_All';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchClients()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_Clients';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchDomains()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_Domains';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }
    
    /**
     * returns new searchIndex instance
     * 
     * @return Core_Model_Dataprovider_Interface_SearchIndex
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchIndex()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_SearchIndex';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_SearchIndex) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search index implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchNews()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_News';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }
    
    /**
     * returns new search instance
     * 
     * @return Core_Model_Dataprovider_Interface_Search
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newSearchNodes()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_Search_Nodes';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_Search) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid search implementation.'
        );
    }

    /**
     * create new user instance
     * 
     * @return Core_Model_Dataprovider_Interface_User
     * @throws Core_Model_DataProvider_Exception
     */
    static public function newUser()
    {
        $currentAdapter = self::getAdapter();
        $className = self::PROVIDER_CLASS_PATH_PRE . $currentAdapter . '_User';

        $newOne = new $className();
        if ($newOne instanceof Core_Model_Dataprovider_Interface_User) {
            return $newOne;
        }
        
        throw new Core_Model_DataProvider_Exception(
            'The data provider: ' . $currentAdapter . ' doesn\'t have a valid user implementation.'
        );
    }
    
    /**
     * resets instance
     */
    static public function reset()
    {
        self::resetAdapter();
        self::setAdmin();
        self::setClient();
        self::setConnection();
        self::setConfig();
        self::setDomain();
        self::setLog();
        self::setModule();
        self::setNews();
        self::setNode();
        self::setSearchAdmins();
        self::setSearchAll();
        self::setSearchClients();
        self::setSearchDomains();
        self::setSearchIndex();
        self::setSearchNodes();
        self::setUser();
    }
    
   /**
     * resets current adapter to the default adapter
     */
    public static function resetAdapter()
    {
        self::$_adapter = self::DEFAULT_ADAPTER;
    }
    
    /**
     * set adapter
     * 
     * resets allready builded objects
     * 
     * @param string $adapter
     */
    static public function setAdapter($adapter = NULL)
    {
        self::reset();
        
        self::$_adapter = (string) $adapter;
    }
    
    /**
     * sets admin instance
     * 
     * @param Core_Model_Dataprovider_Interface_Admin $admin
     */
    static public function setAdmin(Core_Model_Dataprovider_Interface_Admin $admin = null)
    {
        self::$_admin = $admin;
    }
    
    /**
     * sets client instance
     * 
     * @param Core_Model_Dataprovider_Interface_Client $client
     */
    static public function setClient(Core_Model_Dataprovider_Interface_Client $client = null)
    {
        self::$_client = $client;
    }
    
    /**
     * sets config instance
     * 
     * @param Core_Model_Dataprovider_Interface_Config $config 
     */
    static public function setConfig(Core_Model_Dataprovider_Interface_Config $config = null)
    {
        self::$_config = $config;
    }
    
    /**
     * @param Core_Model_Dataprovider_Interface_Connection $connection
     */
    static public function setConnection(Core_Model_Dataprovider_Interface_Connection $connection = null)
    {
        self::$_connection = $connection;
    }
    
    /**
     * sets domain instance
     * 
     * @param Core_Model_Dataprovider_Interface_Domain $domain 
     */
    static public function setDomain(Core_Model_Dataprovider_Interface_Domain $domain = null)
    {
        self::$_domain = $domain;
    }
    
    /**
     * sets log instance
     * 
     * @param Core_Model_Dataprovider_Interface_Log $log
     */
    static public function setLog(Core_Model_Dataprovider_Interface_Log $log = null)
    {
        self::$_log = $log;
    }
    
    /**
     * sets module instance
     * 
     * @param Core_Model_Dataprovider_Interface_Module $module
     */
    static public function setModule(Core_Model_Dataprovider_Interface_Module $module = null)
    {
        self::$_module = $module;
    }
    
    /**
     * sets news instance
     * 
     * @param Core_Model_Dataprovider_Interface_News $news
     */
    static public function setNews(Core_Model_Dataprovider_Interface_News $news = null)
    {
        self::$_news = $news;
    }
    
    /**
     * sets node instance
     * 
     * @param Core_Model_Dataprovider_Interface_Node $node
     */
    static public function setNode(Core_Model_Dataprovider_Interface_Node $node = null)
    {
        self::$_node = $node;
    }
    
    /**
     * sets search admin instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchAdmins(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchAdmins = $search;
    }
    
    /**
     * sets search all instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchAll(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchAll = $search;
    }
    
    /**
     * sets search clients instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchClients(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchClients = $search;
    }
    
    /**
     * sets search domain instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchDomains(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchDomains = $search;
    }
    
    /**
     * sets search index instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchIndex(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchIndex = $search;
    }
    
    /**
     * sets search nodes instance
     * 
     * @param Core_Model_Dataprovider_Interface_Search $search
     */
    static public function setSearchNodes(Core_Model_Dataprovider_Interface_Search $search = null)
    {
        self::$_searchNodes = $search;
    }
    
    /**
     * sets user instance
     * 
     * @param Core_Model_Dataprovider_Interface_User $user
     */
    static public function setUser(Core_Model_Dataprovider_Interface_User $user = null)
    {
        self::$_user = $user;
    }
    
}

