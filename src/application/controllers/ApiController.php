<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiController extends Zend_Controller_Action
{

    /**
     * current requested api key
     * 
     * @var string
     */
    protected $_apiKey;
    
    /**
     * gets Node from api request
     * 
     * @return Core_Model_ValueObject_Node|null
     */
    public function _getApiNode()
    {
        if(!($this->_apiKey) || 
                !($node = Core_Model_DiFactory::getNodeManager()->getNodeByApiKey($this->_apiKey))) {
            return null;
        }
        
        return $node;
    }
    
    public function init()
    {
        $this->_apiKey = $this->getRequest()->getHeader('X-Maze-Node-Api');
    }

    public function commandsnodeserviceAction()
    {
        if(!($node = $this->_getApiNode())) {
            return $this->setForbidden();
        }
        
        $service = $this->getParam('serviceName');
        if(!$node->hasService($service)) {
            return $this->setNotFound();
        }

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->_helper->layout()->disableLayout();

        $this->view->service = $service;
        $this->view->reportHash = $node->getReportHash($service);
        $this->view->commands = $node->getCommands($service);
    }
    
    public function reportnodeserviceAction()
    {
        if(!($node = $this->_getApiNode())) {
            return $this->setForbidden();
        }
        
        $service = $this->getParam('serviceName');
        if(!$node->hasService($service)) {
            return $this->setNotFound();
        }
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        if(!Core_Model_DiFactory::getApiManager()->reportNodeService($this->_apiKey, $service, $this->getParam('report'))) {
            $this->getResponse()->setHttpResponseCode(500);
        };
    }
    
    /**
     * sets forbidden http code in response object and disable rendering
     */
    public function setForbidden()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $this->getResponse()->setHttpResponseCode(403);
    }
    
    /**
     * sets forbidden http code in response object and disable rendering
     */
    public function setNotFound()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $this->getResponse()->setHttpResponseCode(404);
    }
    
}

