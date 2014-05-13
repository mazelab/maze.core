<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * NodesController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class NodesController extends Zend_Controller_Action
{

    /**
     * message when api request for node was not found
     */
    CONST MESSAGE_API_REQUEST_NOT_FOUND = 'Api request for %1$s not found';
    
    /**
     * message when node wasn't found
     */
    CONST MESSAGE_NODE_NOT_FOUND = 'Node %1$s not found';
    
    /**
     * navigation label for domain registering
     */
    CONST NAVIGATION_LABEL_REGISTER = 'Register %1$s';
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('detail', 'json')
                    ->addActionContext('deleteadditionalinfo', 'json')
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('addservice', 'json')
                    ->addActionContext('addadditionalfield', 'json')
                    ->addActionContext('removeservice', 'json')
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $apiManager = Core_Model_DiFactory::getApiManager();
        $pager = Core_Model_DiFactory::getSearchNodes();
        $pager->setLimit($this->getParam('limit', 10));
        
        if($this->getParam('term')) {
            $pager->setSearchTerm($this->getParam('term'));
        }
        
        $action = $this->getParam('pagerAction');
        if($action == 'last') {
            $pager->last();
        } else {
            $pager->setPage($this->getParam('page', 1))->page();
        }
        
        $this->view->pager = $pager->asArray();
        $this->view->addBasePath(APPLICATION_PATH . '/layouts');
        $this->view->unregisteredApis = $apiManager->getUnregisteredApiRequests();
    }

    public function detailAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNode($this->getParam('nodeId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NODE_NOT_FOUND, $this->getParam('nodeId'));
            return $this->_forward('index');
        }
        
        $form = new Core_Form_Node();
        $form->setAdditionalFields($node);

        if ($this->getRequest()->isPost()) {
            $values = $form->getValidValues($this->_request->getPost());

            if (!empty($values)) {
                $this->view->result = $nodeManager->updateNode($node->getId(), $values);
            }

            $this->view->formErrors = $form->getMessages();
        }

        $nodeData = $node->getData();
        
        $this->view->node = $nodeData;
        $this->view->nodeId = $node->getId();
        $this->view->services = $node->getServices();
        $this->view->form = $form->setDefaults($nodeData);
        $this->view->logs = Core_Model_DiFactory::getLogManager()->getNodeLogs($node->getId());
        
        $serviceForm = new Core_Form_NodeServices();
        $this->view->serviceForm = $serviceForm->setServiceSelect($node);
        $this->view->clients = Core_Model_DiFactory::getModuleListings()
                ->getClientsWithDomainsByNode($node->getId());
        $this->view->domains = Core_Model_DiFactory::getModuleListings()
                ->getDomainsWithClientsByNode($node->getId());

        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $active["page"]->setLabel($form->getValue("name"));
        }
    }

    public function deleteAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $this->view->status = false;
        
        if(($node = $nodeManager->getNodeByName($this->_getParam('nodeName')))) {
            $this->view->status = $nodeManager->deleteNode($node->getId());
        }
    }
    
    public function addserviceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNodeByName($this->getParam('nodeName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NODE_NOT_FOUND, $this->getParam('nodeName'));
            return null;
        }
        
        if($this->_request->getPost()) {
            $form = new Core_Form_NodeServices();
            $form->setServiceSelect($node);
            
            if($form->isValid($this->_request->getPost())) {
                $this->view->status = $nodeManager->addService($node->getId(), $form->getValue('service'));
                
                if(($service = Core_Model_DiFactory::getModuleRegistry()->getModule($form->getValue('service')))) {
                    if($service->getModuleConfig('routes/config/node/route')) {
                        $this->view->configNodeRoute = $this->view->url(array($node->getName()),
                            $service->getModuleConfig('routes/config/node/route'));
                    }
                    $this->view->service = $service->getModuleConfig();
                }
            }
            
            $this->view->formErrors = $form->getMessages();
        }
    }
    
    public function addadditionalfieldAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $this->view->result = false;
        
        if(!($node = $nodeManager->getNodeByName($this->getParam('nodeName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NODE_NOT_FOUND, $this->getParam('nodeName'));
            return;
        }
        
        $form = new Core_Form_AdditionalInfo();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->_request->getPost())) {
                $this->view->result = $nodeManager
                        ->addAdditionalField($node->getId(), $form->getValues());
            } else {
                $this->view->formErrors = $form->getMessages();
            }
        }
    }

    public function registerapiAction()
    {
        $apiManager = Core_Model_DiFactory::getApiManager();
        if(!($request = $apiManager->getUnregisteredApiRequest($this->getParam('nodeName'))) ||
                !array_key_exists('data', $request)) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_API_REQUEST_NOT_FOUND, $this->getParam('nodeName'));
            return $this->_forward('index');
        }
        
        $form = new Core_Form_AddNode();
        $data = $request["data"];

        if($this->getRequest()->isPost()) {
            $data["nodetype"] = $this->getParam("nodetype", null);

            if($form->isValid($data)) {
                $nodeManager = Core_Model_DiFactory::getNodeManager();
                
                if($nodeManager->createNode($form->getValues())){
                    return $this->redirect($this->view->url(array(), 'nodes'));
                }
            }
        }

        $this->view->request = $request;
        $this->view->form = $form->setDefault('apiKey', $data['apiKey']);

        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $active["page"]->setLabel($this->view
                    ->translate(self::NAVIGATION_LABEL_REGISTER, $this->getParam('nodeName')));
        }
    }
    
    public function removeserviceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $serviceName = $this->getParam("serviceName");
        $nodeName = $this->getParam("nodeName");

        if(!($node = $nodeManager->getNodeByName($nodeName))) {
            return null;
        }

        $this->view->result = $nodeManager->removeNodeService($node->getId(), $serviceName);
    }
}

