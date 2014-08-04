<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_NodeManager
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_NodeManager
{
    
    /**
     * message when node was activated
     */
    CONST MESSAGE_NODE_ACTIVATED = 'Node %1$s was activated';
    
    /**
     * message when node was created
     */
    CONST MESSAGE_NODE_CREATED = 'Node %1$s was created';
    
    /**
     * message when node was deactivated
     */
    CONST MESSAGE_NODE_DEACTIVATED = 'Node %1$s was deactivated';

    /**
     * message when node was deleted
     */
    CONST MESSAGE_NODE_DELETED = 'Node %1$s was deleted';
    
    /**
     * message when service was added to a node
     */
    CONST MESSAGE_NODE_SERVICE_ADD = 'Service %1$s was added to node %2$s';

    /**
     * message when node was updated
     */
    CONST MESSAGE_NODE_UPDATED = 'Node %1$s was updated';
    
    /**
     * @return Core_Model_Logger
     */
    protected function _getLogger()
    {
        return Core_Model_DiFactory::getLogger();
    }
    
    /**
     * returns a certain node instance if registered
     * 
     * @param string $nodeId
     * @return Core_Model_ValueObject_Node|null null if not registered
     */
    protected function _getRegisteredNode($nodeId)
    {
        if(!$this->isNodeRegistered($nodeId)) {
            return null;
        }
        
        return Core_Model_DiFactory::getNode($nodeId);
    }
    
    /**
     * loads and registers a certain node instance
     * 
     * @param string $nodeId
     * @return boolean
     */
    protected function _loadNode($nodeId)
    {
        if(!$nodeId) {
            return null;
        }
        
        $data = $this->getProvider()->getNode($nodeId);
        if(empty($data)) {
            return false;
        }
        
        return $this->registerNode($nodeId, $data);
    }
    
    /**
     * loads and registers a certain node instance by api key
     * 
     * @param string $apiKey
     * @return boolean
     */
    protected function _loadNodeByApiKey($apiKey)
    {
        if(!$apiKey) {
            return null;
        }
        
        $data = $this->getProvider()->getNodeByApiKey($apiKey);
        if(empty($data) || !array_key_exists('_id', $data))  {
            return null;
        }
        
        if(!$this->registerNode($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }
    
    /**
     * loads and registers a certain node instance by name
     * 
     * @param string $nodeName
     * @return boolean
     */
    protected function _loadNodeByName($nodeName)
    {
        if(!$nodeName) {
            return null;
        }
        
        $data = $this->getProvider()->getNodeByName($nodeName);
        if(empty($data) || !array_key_exists('_id', $data))  {
            return null;
        }
        
        if(!$this->registerNode($data['_id'], $data)) {
            return null;
        }
        
        return $data['_id'];
    }

    /**
     * updates additional fields
     *
     * services schema:
     * array(
     *     'e3d704f3542b44a621ebed70dc0efe13' => array( # update/set additional field
     *          'label' => 'test1',
     *          'value' => 'test'
     * ),
     *     'e3d704f3542b44a621ebed70dc0efe15' => array( # remove additional field
     *          'value' =>
     * )
     *
     * @param string $nodeId
     * @param array $data
     * @return boolean
     */
    public function _updateAdditionalFields($nodeId, array $data)
    {
        if(!$data) {
            return true;
        }
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }

        $updateData = $data;
        foreach($data as $key => $additionalField) {
            if(!array_key_exists('value', $additionalField)) {
                unset($updateData[$key]);
                continue;
            }

            if($additionalField['value']) {
                if(!$node->getData("additionalFields/$key")) {
                    return false;
                } elseif (array_key_exists('label', $additionalField)) {
                    unset($updateData[$key]['label']);
                }
            } else {
                if(!$node->deleteAdditionalField($key)) {
                    return false;
                }
                unset($updateData[$key]);
            }

        }

        if($updateData) {
            $node->setData(array('additionalFields' => $updateData));
        }

        return true;
    }

    /**
     * updates node services via boolean values
     *
     * services schema:
     * array(
     *     'service1' => true,          # enables service1
     *     'service2' => false          # disables service2
     * )
     *
     * @param string $nodeId
     * @param array $services
     * @return boolean
     */
    public function _updateNodeServices($nodeId, array $services)
    {
        foreach($services as $service => $state) {
            if(!filter_var($state, FILTER_VALIDATE_BOOLEAN)) {
                if(!$this->removeNodeService($nodeId, $service)) {
                    return false;
                }
            } else {
                if(!$this->addService($nodeId, $service)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * adds an additional field to the given node
     * 
     * @param string $nodeId
     * @param array $data
     * @return boolean|string id of additional field
     */
    public function addAdditionalField($nodeId, $data)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }

        if (!array_key_exists("additionalKey", $data) || !array_key_exists("additionalValue", $data)
                || !($additionalId = $node->addAdditionalField($data['additionalKey'], $data['additionalValue']))){
            return false;
        }
        
        if (!$node->save()) {
            return false;
        }
        
        return $additionalId;
    }
    
    /**
     * adds a certain service to a certain node
     * 
     * @param string $nodeId
     * @param string $serviceName
     * @return boolean
     */
    public function addService($nodeId, $serviceName)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }
        if($node->hasService($serviceName)) {
            return true;
        }
        
        if(!$node->addService($serviceName)) {
            return false;
        }

        $module = Core_Model_DiFactory::getModuleRegistry()->getModule($serviceName);
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
                ->setMessage(self::MESSAGE_NODE_SERVICE_ADD)
                ->setMessageVars($module->getLabel(), $node->getName())
                ->setNodeRef($nodeId)->save();
        
        return true;
    }
    
    /**
     * creates a new node
     * 
     * @param array $data
     * @return string|boolean nodeId on success
     */
    public function createNode($data)
    {
        $node = Core_Model_DiFactory::newNode();
        $data["status"] = true;

        if(!Core_Model_DiFactory::getModuleApi()->preAddNode($data) || !$node->setData($data)->save()) {
            return false;
        }

        $this->registerNode($node->getId(), $node);

        if (isset($data["apiKey"])){
            Core_Model_DiFactory::getApiManager()->registerApiKey($node->getName(), $node->getIp());
        }
        
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
            ->setMessage(self::MESSAGE_NODE_CREATED)
            ->setMessageVars($node->getName())
            ->setNodeRef($node->getId())
            ->save();

        Core_Model_DiFactory::getModuleApi()->postAddNode($node->getId());

        return $node->getId();
    }

    /**
     * deletes a certain additional field from this node in the data backend
     *
     * @param string $nodeId
     * @param mixed $key
     * @return boolean
     */
    public function deleteAdditionalField($nodeId, $key)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }

        if (!$node->deleteAdditionalField($key) || !$node->save())  {
            return false;
        }

        return true;
    }

    /**
     * deletes a certain node
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function deleteNode($nodeId)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }
        
        if(!$this->removeNodeServices($nodeId)) {
            return false;
        }
        if(!$this->getProvider()->deleteNode($nodeId)) {
            return false;
        }

        Core_Model_DiFactory::getIndexManager()->unsetNode($nodeId);
        $this->unregisterNode($nodeId);
        $this->_getLogger()->setType(Core_Model_Logger::TYPE_WARNING)
                ->setMessage(self::MESSAGE_NODE_DELETED)
                ->setMessageVars($node->getName())
                ->save();
        
        return true;
    }
    
    /**
     * returns the last modificated nodes
     * 
     * @param  integer $limit limit the search result
     * @return array
     */
    public function getLastModifiedNodesAsArray($limit = 5)
    {
        if(!($nodes = $this->getProvider()->getNodesByLastModification($limit)) || !is_array($nodes)) {
            return array();
        }

        return $nodes;
    }
    
    /**
     * returns a certain node
     * 
     * @param string $nodeId
     * @return Core_Model_ValueObject_Node|null
     */
    public function getNode($nodeId)
    {
        if(!$nodeId) {
            return null;
        }
        
        if(!$this->isNodeRegistered($nodeId)) {
            $this->_loadNode($nodeId);
        }
        
        return $this->_getRegisteredNode($nodeId);
    }
    
    /**
     * returns the data set of a certain node
     * 
     * @param string $nodeId
     * @return array
     */
    public function getNodeAsArray($nodeId)
    {
        if(!($node = $this->getNode($nodeId))) {
            return array();
        }
                
        return $node->getData();
    }
    
    /**
     * returns the node identified by the given $apiKey
     * 
     * @param string $apiKey
     * @return Core_Model_ValueObject_Node|null
     */
    public function getNodeByApiKey($apiKey)
    {
        if(($node = Core_Model_DiFactory::getNodeByApiKey($apiKey))) {
            return $node;
        }
        
        if(!($nodeId = $this->_loadNodeByApiKey($apiKey))) {
            return null;
        }
        
        return $this->_getRegisteredNode($nodeId);
    }
    
    /**
     * returns the node identified by the given $apiKey
     * 
     * @param string $apiKey
     * @return array
     */
    public function getNodeByApiKeyAsArray($apiKey)
    {
        if(!($node = $this->getNodeByApiKey($apiKey))) {
            return array();
        }
                
        return $node->getData();
    }

    /**
     * get all nodes of a certain client enriched with api dependencies for api use
     *
     * @param string $clientId
     * @return array
     */
    public function getNodesByClientForApi($clientId)
    {
        $result = array();

        if(!$clientId || !($nodes = Core_Model_DiFactory::getModuleListings()->getNodesWithDomainsByClient($clientId))) {
            return array();
        }

        foreach($nodes as $node) {
            array_push($result, $node);
        }

        return $result;
    }

    /**
     * get all nodes of a certain domain enriched with api dependencies for api use
     *
     * @param string $domainId
     * @return array
     */
    public function getNodesByDomainForApi($domainId)
    {
        $result = array();

        if(!$domainId || !($nodes = Core_Model_DiFactory::getModuleListings()->getNodesWithServicesByDomain($domainId))) {
            return array();
        }

        foreach($nodes as $node) {
            array_push($result, $node);
        }

        return $result;
    }

    /**
     * returns the node identified by the given node name
     * 
     * @param string $nodeName
     * @return Core_Model_ValueObject_Node|null
     */
    public function getNodeByName($nodeName)
    {
        if(($node = Core_Model_DiFactory::getNodeByName($nodeName))) {
            return $node;
        }
        
        if(!($nodeId = $this->_loadNodeByName($nodeName))) {
            return null;
        }
        
        return $this->_getRegisteredNode($nodeId);
    }
    
    /**
     * returns the node identified by the given name
     * 
     * @param string $nodeName
     * @return array
     */
    public function getNodeByNameAsArray($nodeName)
    {
        if(!($node = $this->getNodeByName($nodeName))) {
            return array();
        }
                
        return $node->getData();
    }

    /**
     * gets complete node data enriched with api dependencies for api use
     *
     * @param string $nodeId
     * @return array|null
     */
    public function getNodeForApi($nodeId)
    {
        if(!($node = $this->getNode($nodeId))) {
            return null;
        }

        return $node->getDataForApi();
    }
    
    /**
     * return nodes
     * 
     * @return array conains Core_Model_ValueObject_Node
     */
    public function getNodes()
    {
        $nodes = array();

        foreach($this->getProvider()->getNodes() as $nodeId => $node) {
            $this->registerNode($nodeId, $node);
            $nodes[$nodeId] = $this->_getRegisteredNode($nodeId);
        }
            
        return $nodes;
    }
    
    /**
     * return nodes as array
     * 
     * @return array
     */
    public function getNodesAsArray()
    {
        $nodes = array();

        foreach($this->getProvider()->getNodes() as $nodeId => $node) {
            $this->registerNode($nodeId, $node);
            $nodes[$nodeId] = $node;
        }
            
        return $nodes;
    }
    
    /**
     * get nodes which have the certain service
     * 
     * @param string $serviceName
     * @return array contains instances of Core_Model_ValueObject_Node
     */
    public function getNodesByService($serviceName)
    {
        $nodes = array();
        
        foreach($this->getProvider()->getNodesByService($serviceName) as $nodeId => $node) {
            if(!$this->_getRegisteredNode($nodeId)) {
                $this->registerNode($nodeId, $node);
            }
            
            $nodes[$nodeId] = $this->getNode($nodeId);
        }
        
        return $nodes;
    }
    
    /**
     * get nodes which have the certain service as array
     * 
     * @param string $serviceName
     * @return array contains instances of Core_Model_ValueObject_Node
     */
    public function getNodesByServiceAsArray($serviceName)
    {
        $nodes = array();
        
        foreach($this->getNodesByService($serviceName) as $nodeId => $node) {
            $nodes[$nodeId] = $node->getData();
        }
        
        return $nodes;
    }
    
    /**
     * @return Core_Model_Dataprovider_Interface_Node
     */
    public function getProvider()
    {
        return Core_Model_Dataprovider_DiFactory::getNode();
    }
    
    /**
     * checks if a certain node instance is allready registered
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function isNodeRegistered($nodeId)
    {
        if(Core_Model_DiFactory::isNodeRegistered($nodeId)) {
            return true;
        }
        
        return false;
    }

    /**
     * paginate nodes
     *
     * example return:
     * array(
     *      'total' => 20,
     *      'data' => array(0 => array(...), ...)
     * )
     *
     * @param int $numPerPage
     * @param int $page
     * @param string $term
     * @return array
     */
    public function paginate($numPerPage = 10, $page = 1, $term = null)
    {
        if(!is_numeric($page)) {
            return array();
        }

        return $this->getProvider()->paginate($numPerPage, $page, $term);
    }

    /**
     * registers a node instance
     * 
     * overwrites existing instances
     * 
     * @param string $nodeId
     * @param mixed $context array or Core_Model_ValueObject_Node
     * @param boolean $setLoadedFlag only when $context is array states if
     * loading flag will be set to avoid double loading
     * @return boolean
     */
    public function registerNode($nodeId, $context, $setLoadedFlag = true)
    {
        $node = null;
        
        if(is_array($context)) {
            $node = Core_Model_DiFactory::newNode($nodeId);
            
            if($setLoadedFlag) {
                $node->setLoaded(true);
            }
            
            $node->getBean()->setBean($context);
        } elseif($context instanceof Core_Model_ValueObject_Node) {
            $node = $context;
        }
        
        if(!$node) {
            return false;
        }
        
        Core_Model_DiFactory::registerNode($nodeId, $node);
        
        return true;
    }

    /**
     * remove a certain service on all nodes
     *
     * @param string $service name of the service
     * @return boolean
     */
    public function removeNodesService($service)
    {
        foreach(array_keys($this->getNodesByService($service)) as $nodeId) {
            if(!$this->removeNodeService($nodeId, $service)) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a all node services
     *
     * @param string $nodeId id of the node
     * @return boolean
     */
    public function removeNodeServices($nodeId)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }
        if(!($services = $node->getServices())) {
            return true;
        }

        foreach(array_keys($services) as $serviceName) {
            if(!$this->removeNodeService($nodeId, $serviceName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * remove a certain node service
     *
     * @param string $nodeId id of the node
     * @param string $service name of the service
     * @return boolean
     */
    public function removeNodeService($nodeId, $service)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }

        return $node->removeService($service);
    }

    /**
     * updates a certain node with the given data
     *
     * @param string $nodeId
     * @param array $data
     * @return boolean
     */
    public function updateNode($nodeId, $data)
    {
        if(!($node = $this->getNode($nodeId))) {
            return false;
        }

        if(array_key_exists('services', $data) && is_array($data['services'])) {
            if(!$this->_updateNodeServices($nodeId, $data['services'])) {
                return false;
            }
            unset($data['services']);
        }

        if(array_key_exists('additionalFields', $data) && is_array($data['additionalFields'])) {
            if(!$this->_updateAdditionalFields($nodeId, $data['additionalFields'])) {
                return false;
            }
            unset($data['additionalFields']);
        }

        if (isset($data['additionalKey']) && isset($data['additionalValue'])) {
            $node->addAdditionalField($data['additionalKey'], $data['additionalValue']);
            unset($data['additionalKey'], $data['additionalValue']);
        }

        if(!$node->setData($data)->save()) {
            return false;
        }

        $this->_getLogger()->setType(Core_Model_Logger::TYPE_NOTIFICATION)
            ->setMessage(self::MESSAGE_NODE_UPDATED)
            ->setMessageVars($node->getName())
            ->setData($data)->setNodeRef($node->getId())->save();

        return true;
    }

    /**
     * unregisters a certain node instance
     * 
     * @param string $nodeId
     * @return boolean
     */
    public function unregisterNode($nodeId)
    {
        if(!$this->_getRegisteredNode($nodeId)) {
            return true;
        }
        
        Core_Model_DiFactory::unregisterNode($nodeId);
    }

}
