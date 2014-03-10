<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Node_Commands
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Node_Commands
{
    
    /**
     * @var string
     */
    protected $_nodeId;

    /**
     * constructer sets node id if given
     * 
     * @param string $nodeId
     */
    public function __construct($nodeId = null)
    {
        if($nodeId) {
            $this->setNodeId($nodeId);
        };
    }
    
    /**
     * gets current node
     * 
     * @return Core_Model_ValueObject_Node|null
     */
    protected function _getNode()
    {
        if(!$this->getNodeId()) {
            return null;
        }
        
        return Core_Model_DiFactory::getNodeManager()->getNode($this->getNodeId());
    }
    
    /**
     * resolves context array into a simple array struct
     * 
     * @param array $commands
     * @return array
     */
    protected function _resolveContext(array $commands)
    {
        $resolvedContext = array();
        foreach($commands as $command) {
            if(!is_array($command)) {
                continue;
            }
            
            foreach($command as $subCommand) {
                array_push($resolvedContext, $subCommand);
            }
        }
        
        return $resolvedContext;
    }

    /**
     * adds new commands to a certain service
     * 
     * @param string $service
     * @param array|string $commands one as string and multiple commands as array
     * @return boolean
     */
    public function addCommands($service, $commands)
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        if(!is_string($commands) && !is_array($commands)) {
            return false;
        } elseif (is_string($commands)) {
            $commands[] = $commands;
        }
        
        if(($olderCommands = $this->getNormalCommands($service))) {
            $commands = array_merge($olderCommands, $commands);
        }
        
        $data = array(
            "services/${service}/commands" => $commands
        );
        
        $node->setData($data);

        return true;
    }
    
    /**
     * adds new context commands identified by key to a certain service
     * 
     * @param string $service
     * @param string $key
     * @param array|string $commands one as string and multiple commands as array
     * @return boolean
     */
    public function addContextCommands($service, $key, $commands)
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        if(!is_string($commands) && !is_array($commands)) {
            return false;
        } elseif (is_string($commands)) {
            $commands[] = $commands;
        }
        
        $md5Key = md5($key);
        $node->unsetProperty("services/${service}/contextCommands/{$md5Key}");
        
        $data = array(
            "services/${service}/contextCommands/{$md5Key}" => $commands
        );
        
        $node->setData($data);

        return true;
    }
    
    /**
     * clears commands entry of a certain service
     * 
     * @param string $service
     * @return boolean
     */
    public function removeCommands($service)
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        $node->unsetProperty("services/${service}/commands");
        $node->unsetProperty("services/${service}/contextCommands");
        
        return true;
    }
    
    /**
     * clears commands entry of a certain service
     * 
     * @param string $service
     * @return boolean
     */
    public function removeContextCommand($service, $key)
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        $node->unsetProperty("services/${service}/contextCommands");
        
        return true;
    }
    
    /**
     * gets commands of a certain service
     * 
     * returns normal and context commands
     * 
     * @param string $service
     * @return array
     */
    public function getCommands($service)
    {
        $commands = $this->getNormalCommands($service);
        
        foreach($this->getContextCommands($service, true) as $contextCommand) {
            array_push($commands, $contextCommand);
        }
        
        return $commands;
    }
    
    /**
     * gets context commands of a certain service
     * 
     * only commands from services/service/contextCommands property
     * 
     * @param string $service
     * @param boolean $resolveContext returns only simple array without context keys
     * @return array
     */
    public function getContextCommands($service, $resolveContext = false)
    {
        if(!($node = $this->_getNode())) {
            return array();
        }
        
        if(!($commands = $node->getData("services/${service}/contextCommands")) ||
                !is_array($commands)) {
            return array();
        }
        
        if($resolveContext) {
            return $this->_resolveContext($commands);
        }
        
        return $commands;
    }
    
    /**
     * gets current node id
     * 
     * @return string|null
     */
    public function getNodeId()
    {
        return $this->_nodeId;
    }
    
    /**
     * gets commands of a certain service
     * 
     * only commands from services/service/commands property
     * 
     * @param string $service
     * @return array
     */
    public function getNormalCommands($service)
    {
        if(!($node = $this->_getNode())) {
            return array();
        }
        
        if(!($commands = $node->getData("services/${service}/commands")) ||
                !is_array($commands)) {
            return array();
        }
        
        return $commands;
    }
    
    /**
     * overwrites current commands of a certain service
     * 
     * @param string $service
     * @param array $commands
     * @return boolean
     */
    public function setCommands($service, array $commands)
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        $data = array(
            "services/${service}/commands" => $commands
        );
        
        $node->setData($data);
        
        return true;
    }
    
    /**
     * saves changes in node
     * 
     * @return boolean
     */
    public function save()
    {
        if(!($node = $this->_getNode())) {
            return false;
        }
        
        return $node->save();
    }
    
    /**
     * sets given node id for command options
     * 
     * @param string $nodeId
     * @return self
     */
    public function setNodeId($nodeId = null)
    {
        $this->_nodeId = $nodeId;
    }
    
}
