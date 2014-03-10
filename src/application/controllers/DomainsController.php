<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * DomainsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class DomainsController extends Zend_Controller_Action
{

    /**
     * message when domain wasn't found
     */
    CONST MESSAGE_DOMAIN_NOT_FOUND = 'Domain %1$s not found';
    
    /**
     * navigation label for domain registering
     */
    CONST NAVIGATION_LABEL_REGISTER = 'Register %1$s';
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('detail', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('addadditionalfield', 'json')
                    ->addActionContext('addservice', 'json')
                    ->addActionContext('removeservice', 'json')
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $pager = Core_Model_DiFactory::getSearchDomains();
        $pager->setLimit($this->getParam('limit', 10));
        
        if($this->getParam('term'))
            $pager->setSearchTerm($this->getParam('term'));
        
        $action = $this->getParam('pagerAction');
        if($action == 'last') {
            $pager->last();
        } else {
            $pager->setPage($this->getParam('page', 1))->page();
        }
        
        $this->view->pager = $pager->asArray();
        $this->view->addBasePath(APPLICATION_PATH . '/layouts');
    }

    public function detailAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $domainName = $this->_getParam('domainName');
        
        if(!($domain = $domainManager->getDomainByName($domainName))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $domainName);
            return $this->_forward('index');
        }
        
        $clientManager = Core_Model_DiFactory::getClientManager();
        $serviceForm = new Core_Form_DomainServices();
        $form = new Core_Form_Domain();
        
        $form->setAdditionalFields($domain);
        if ($this->getRequest()->isPost()) {
            $values = $form->getValidValues($this->_request->getPost());
            
            if(!empty($values)) {
                $this->view->result = $domainManager->updateDomain($domain->getId(), $values);
            }
            
            $this->view->formErrors = $form->getMessages();
        }
        
        $this->view->domain = $domain->getData();
        $this->view->domainId = $domain->getId();
        $this->view->form = $form->setDefaults($domain->getData());
        $this->view->serviceForm = $serviceForm->setServiceSelect($domain);
        $this->view->owner = $clientManager->getClientByDomainAsArray($domain->getId());
        $this->view->services = $domain->getServices();
        
        $this->view->logs = Core_Model_DiFactory::getLogManager()->getDomainLogs($domain->getId());
        $this->view->nodes = Core_Model_DiFactory::getModuleListings()
                ->getNodesWithServicesByDomain($domain->getId());
        
        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $active["page"]->setLabel($form->getValue("name"));
        }
    }
    
    public function deleteAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $this->view->status = false;
        
        if(($domain = $domainManager->getDomainByName($this->_getParam('domainName')))) {
            $this->view->status = $domainManager->deleteDomain($domain->getId());
        }
    }

    public function addAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $form = new Core_Form_AddDomain();
        
        if ($this->_request->getPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $domainId = $domainManager->createDomain($form->getValue('name'),
                        $form->getValue('owner'), $form->getValue('procurementplace'));

                if($domainId) {
                    $this->view->result = true;
                    $this->view->domainId = $domainId;
                    
                    $this->_redirect($this->view->url(array(), 'domains'));
                }
            } else {
                $this->view->formErrors = $form->getMessages();
            }
        }
        
        if($this->getParam('owner', false) && $this->_request->isXmlHttpRequest()) {
            $form->showOnlyDomainAndDisableSelectbox($this->getParam('owner'));
        }

        $this->view->form = $form;
    }

    public function addadditionalfieldAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $this->view->result = false;
        
        if(!($domain = $domainManager->getDomainByName($this->getParam('domainName')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_DOMAIN_NOT_FOUND, $this->getParam('domainName'));
            return;
        }
        
        $form = new Core_Form_AdditionalInfo();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->_request->getPost())) {
                $this->view->result = $domainManager
                        ->addAdditionalField($domain->getId(), $form->getValues());
            } else {
                $this->view->formErrors = $form->getMessages();
            }
        }
    }

    public function addserviceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomainByName($this->getParam('domainName')))) {
            return null;
        }
        
        if($this->_request->getPost()) {
            $form = new Core_Form_DomainServices();
            $form->setServiceSelect($domain);
            
            if($form->isValid($this->_request->getPost()) ) {
                $this->view->status = $domainManager
                        ->addService($domain->getId(), $form->getValue('service'));
                
                if(($service = Core_Model_DiFactory::getModuleRegistry()->getModule($form->getValue('service')))) {
                    if($service->getModuleConfig('routes/config/domain/route')) {
                        $this->view->configDomainRoute = $this->view->url(array($domain->getName()),
                            $service->getModuleConfig('routes/config/domain/route'));
                    }
                    $this->view->service = $service->getModuleConfig();
                }
            }
            
            $this->view->formErrors = $form->getMessages();
        }
        
        $this->view->domain = $domain->getData();
    }
    
    public function removeserviceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $domainName = $this->getParam("domainName");
        $serviceName = $this->getParam("serviceName");

        if(!($domain = $domainManager->getDomainByName($domainName))) {
            return null;
        }

        $this->view->result = Core_Model_DiFactory::getDomainManager()->removeDomainService($domain->getId(), $serviceName);
    }
}

