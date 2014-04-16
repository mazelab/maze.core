<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Logger
 * 
 * log types:
 *  - warning
 *  - error
 *  - notify
 *  - conflict
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Logger
{

    /**
     * action property for log
     * 
     * @var string
     */
    protected $_action;
    
    /**
     * client reference (id) for log client context
     * 
     * @var string
     */
    protected $_clientRef;
    
    /**
     * context name for log - just for description
     * 
     * @var string
     */
    protected $_contextName;
    
    /**
     * context data for log
     *
     * @var array
     */
    protected $_data;
    
    /**
     * domain reference (id) for log domain context
     * 
     * @var string
     */
    protected $_domainRef;
    
    /**
     * log message
     * 
     * @var string
     */
    protected $_message;
    
    /**
     * contains message vars for rendering
     * 
     * @var array
     */
    protected $_messageVars;
    
    /**
     * node reference (id) for log node context
     * 
     * @var string
     */
    protected $_nodeRef;
    
    /**
     * module reference (id) for log
     *
     * @var string
     */
    protected $_moduleRef;
    
    /**
     * log type
     * 
     * @var string
     */
    protected $_type;
    
    /**
     * log url
     *
     * @var string
     */
    protected $_url;

    /**
     * action for general apply actions
     */
    CONST ACTION_APPLY = 'apply';
    
    /**
     * action for general tasks actions
     */
    CONST ACTION_TASK = 'task';
    
    /**
     * key name for action
     */
    CONST KEY_ACTION = 'action';
    
    /**
     * key name for client
     */
    CONST KEY_CLIENT = 'client';
    
    /**
     * key name for context id
     */
    CONST KEY_CONTEXTID = 'contextId';
    
    /**
     * key name for context name
     */
    CONST KEY_CONTEXTNAME = 'contextName';
    
    /**
     * key name for data
     */
    CONST KEY_DATA = 'data';
    
    /**
     * key name for datetime
     */
    CONST KEY_DATETIME = 'datetime';
    
    /**
     * key name for domain
     */
    CONST KEY_DOMAIN = 'domain';
    
    /**
     * key name for message
     */
    CONST KEY_MESSAGE = 'message';
    
    /**
     * key name for message vars
     */
    CONST KEY_MESSAGEVARS = 'messageVars';
    
    /**
     * key name for node
     */
    CONST KEY_NODE = 'node';
    
    /**
     * key name for module
     */
    CONST KEY_MODULE = 'module';
    
    /**
     * key name for type
     */
    CONST KEY_TYPE = 'type';
    
    /**
     * key name for user
     */
    CONST KEY_USER = 'user';
    
    /**
     * key name for user
     */
    CONST KEY_USERID = 'userid';
    
    /**
     * key name for url
     */
    CONST KEY_URL = 'url';
    
    /**
     * log type error
     */
    CONST TYPE_ERROR = 'error';
    
    /**
     * log type for conflict messages
     */
    CONST TYPE_CONFLICT = 'conflict';
    
    /**
     * log type for notify messages
     */
    CONST TYPE_NOTIFICATION = 'notify';
    
    /**
     * log type for success messages
     */
    CONST TYPE_SUCCESS = 'success';
    
    /**
     * log type for warning messages
     */
    CONST TYPE_WARNING = 'warning';
    
    /**
     * system user for actions which are not initialised from any user
     */
    CONST SYSTEM_USER = 'system';
    
    /**
     * builds log data
     * 
     * @return array|null
     */
    protected function _buildLogData()
    {
        $logData = array();
        
        $logData[self::KEY_TYPE] = $this->getType();
        $logData[self::KEY_DATETIME] = $this->_getDatetime();
        $logData[self::KEY_MESSAGE] = $this->getMessage();
        $logData[self::KEY_MESSAGEVARS] = $this->getMessageVars();
        $logData[self::KEY_CONTEXTNAME] = $this->getContextName();
        $logData[self::KEY_USER] = $this->getIdentityLabel();
        $logData[self::KEY_USERID] = $this->getIdentityId();
        $logData[self::KEY_URL] = $this->getUrl();
        $logData[self::KEY_DATA] = $this->getData();
        $logData[self::KEY_ACTION] = $this->getAction();
        
        if($this->getModuleRef() && ($module = Core_Model_DiFactory::getModuleManager()
                    ->getModule($this->getModuleRef()))) {
            $logData[self::KEY_MODULE]['name'] = $module->getName();
            $logData[self::KEY_MODULE]['label'] = $module->getLabel();
        } else {
            $logData[self::KEY_MODULE]['name'] = 'core';
            $logData[self::KEY_MODULE]['label'] = 'core';
        }
        
        if($this->getNodeRef() && ($node = Core_Model_DiFactory::getNodeManager()
                    ->getNode($this->getNodeRef()))) {
            $logData[self::KEY_NODE]['id'] = $this->getNodeRef();
            $logData[self::KEY_NODE]['label'] = $node->getName();
        }
        
        if($this->getClientRef() && ($client = Core_Model_DiFactory::getClientManager()
                    ->getClient($this->getClientRef()))) {
            $logData[self::KEY_CLIENT]['id'] = $this->getClientRef();
            $logData[self::KEY_CLIENT]['label'] = $client->getLabel();
        }
        
        if($this->getDomainRef() && ($domain = Core_Model_DiFactory::getDomainManager()
                    ->getDomain($this->getDomainRef()))) {
            $logData[self::KEY_DOMAIN]['id'] = $this->getDomainRef();
            $logData[self::KEY_DOMAIN]['label'] = $domain->getName();
        }
        
        return $logData;
    }
    
    /**
     * returns standard time
     * 
     * format ISO_8601 - unlocalized
     * 
     * @return string
     */
    protected function _getDatetime()
    {
        $date = new Zend_Date();

        return $date->get(Zend_Date::ISO_8601);
    }
    
    /**
     * checks that builded log data has all requirements
     * 
     * @param string $checkContext
     * @return boolean
     */
    protected function _validateLogData(array $logData, $checkContextId = false)
    {
        $form = new Core_Form_Log();
        
        if($checkContextId) {
            $form->setContextRequirements();
        }
        
        return $form->isValid($logData);
    }
    
    /**
     * gets action property of log
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }
    
    /**
     * gets client id property of log
     * 
     * @return string
     */
    public function getClientRef()
    {
        return $this->_clientRef;
    }
    
    /**
     * gets context name property
     * 
     * @return string
     */
    public function getContextName()
    {
        return $this->_contextName;
    }
    
    /**
     * gets data property of log
     * 
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * gets domain id property of log
     * 
     * @return string
     */
    public function getDomainRef()
    {
        return $this->_domainRef;
    }
    
    /**
     * gets id from zend identity
     * 
     * @return string|null
     */
    public function getIdentityId()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        if(empty($identity) || !array_key_exists('_id', $identity)) {
            return null;
        }
        
        if(!($user = Core_Model_DiFactory::getUserManager()->getUser($identity['_id']))) {
            return null;
        }
        
        return $user->getId();
    }
    
    /**
     * gets username from zend identity
     * if not available it will return system
     * 
     * @return string
     */
    public function getIdentityLabel()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $default = self::SYSTEM_USER;
        
        if(empty($identity) || !array_key_exists('_id', $identity)) {
            return $default;
        }
        
        if(array_key_exists('adminUser', $identity) && array_key_exists('_id', $identity['adminUser'])) {
            $user = Core_Model_DiFactory::getUserManager()->getUser($identity['adminUser']['_id']);
        } else {
            $user = Core_Model_DiFactory::getUserManager()->getUser($identity['_id']);
        }
        
        if(!$user) {
            return $default;
        }
        
        return $user->getLabel();
    }
    
    /**
     * gets message property of log
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * gets message vars
     * 
     * @return array
     */
    public function getMessageVars()
    {
        return $this->_messageVars;
    }
    
    /**
     * gets node id property of log
     * 
     * @return string
     */
    public function getNodeRef()
    {
        return $this->_nodeRef;
    }
    
    /**
     * get provider
     * 
     * @return Core_Model_Dataprovider_Interface_Log
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getLog();
    }
    
    /**
     * gets module id property of log
     * 
     * @return string
     */
    public function getModuleRef()
    {
        return $this->_moduleRef;
    }
    
    /**
     * gets type property of log
     * 
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * gets url property of log
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * resets current log properties
     * 
     * @return Core_Model_NewLogger
     */
    public function reset()
    {
        $this->setType()
             ->setMessage()
             ->setNodeRef()
             ->setContextName()
             ->setClientRef()
             ->setData()
             ->setModuleRef()
             ->setUrl()
             ->setDomainRef()
             ->setAction()
             ->setMessageVars();
        
        return $this;
    }
    
    /**
     * saves log entry in data backend
     * 
     * required fields are:
     * type and message
     * 
     * @return string|null log id
     */
    public function save()
    {
        $logData = $this->_buildLogData();

        $this->reset();
        if(!$logData) {
            return false;
        }
        
        if(!$this->_validateLogData($logData)) {
            return false;
        }
        
        if(!($logId = $this->getProvider()->save($logData))) {
            return false;
        }
        
        return $logId;
    }
    
    /**
     * saves/updates an entry with a contextId and a certain action and type
     * this combination should be seen as a unique entry
     * 
     * if type or action is not given it will use the seted values
     * 
     * @param string $contexId
     * @param string $type
     * @param string $action
     * @return string|null log id
     */
    public function saveByContext($contextId, $type = null, $action = null)
    {
        $logData = $this->_buildLogData();
        
        $this->reset();
        if(!$logData) {
            return false;
        }
        
        $logData[self::KEY_CONTEXTID] = $contextId;
        
        if(!$this->_validateLogData($logData, true)) {
            return false;
        }
        
        if(!$type) {
            $type = $logData[self::KEY_TYPE];
        }
        
        if(!$action) {
            $action = $logData[self::KEY_ACTION];
        }
        
        $logId = $this->getProvider()
                ->saveByContext($contextId, $type, $action, $logData);
        
        if(!$logId) {
            return false;
        }
        
        return $logId;
    }
    
    /**
     * sets action property
     * 
     * @param string $action
     * @return Core_Model_NewLogger
     */
    public function setAction($action = null)
    {
        $this->_action = $action;
        
        return $this;
    }
    
    /**
     * sets client reference with client id in log entry
     * 
     * @param string $clientId
     * @return Core_Model_NewLogger
     */
    public function setClientRef($clientId = null)
    {
        $this->_clientRef = $clientId;
        
        return $this;
    }
    
    /**
     * sets contextName if contextId isn't sufficient to describe context
     * 
     * @param string $contextName
     * @return Core_Model_NewLogger
     */
    public function setContextName($contextName = null)
    {
        $this->_contextName = $contextName;
        
        return $this;
    }    
    
    /**
     * sets context data
     * 
     * @param array $data
     * @return Core_Model_NewLogger
     */
    public function setData(array $data = null)
    {
        $this->_data = $data;
        
        return $this;
    }
    
    /**
     * sets domain regerence with domain id in log entry
     * 
     * @param string $domainId
     * @return Core_Model_NewLogger
     */
    public function setDomainRef($domainId = null)
    {
        $this->_domainRef = $domainId;
        
        return $this;
    }
    
    /**
     * sets message in log entry
     * should be usable with Zend_Translate
     * 
     * @param string $type
     * @return Core_Model_NewLogger
     */
    public function setMessage($message = null)
    {
        $this->_message = $message;
        
        return $this;
    }
    
    /**
     * sets message vars for log entries
     * should be usable with Zend_Translate
     * 
     * if string is given it will be transformed into a array
     * 
     * @param array|string $messageVars
     * @return Core_Model_NewLogger
     */
    public function setMessageVars($vars = null)
    {
        if(func_num_args()) {
            $vars = array();
            foreach(func_get_args() as $arg) {
                array_push($vars, (string) $arg);
            }
        }

        $this->_messageVars = $vars;
        
        return $this;
    }
    
    /**
     * sets node reference with node id in log entry
     * 
     * @param string $nodeId
     * @return Core_Model_NewLogger
     */
    public function setNodeRef($nodeId = null)
    {
        $this->_nodeRef = $nodeId;
        
        return $this;
    }
    
    /**
     * builds route with given Params and sets it as url
     * 
     * @param array $routeParams
     * @param string $routeName
     * @throws Zend_Controller_Exception
     * @return Core_Model_NewLogger
     */
    public function setRoute(array $routeParams = array(), $routeName = null)
    {
        $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        
        $this->setUrl($urlHelper->url($routeParams, $routeName));
        
        return $this;
    }
    
    /**
     * sets module reference with module id for log
     * 
     * @param string $moduleName
     * @return Core_Model_NewLogger
     */
    public function setModuleRef($moduleName = null)
    {
        $this->_moduleRef = $moduleName;
        
        return $this;
    }
    
    /**
     * sets type in log entry
     * 
     * @param string $type
     * @return Core_Model_NewLogger
     */
    public function setType($type = null)
    {
        $this->_type = $type;
        
        return $this;
    }
    
    /**
     * sets context url
     * 
     * @param string $url
     * @return Core_Model_NewLogger
     */
    public function setUrl($url = null)
    {
        $this->_url = $url;
        
        return $this;
    }
    
    /**
     * updates a certain log entry
     * 
     * @param string $logId
     * @return boolean
     */
    public function update($logId)
    {
        $logData = $this->_buildLogData();
        
        $this->reset();
        if(!$logData) {
            return false;
        }
        
        if(!$this->_validateLogData($logData)) {
            return false;
        }
        
        if(!$this->getProvider()->update($logId, $logData)) {
            return false;
        }
        
        return true;
    }
    
}
