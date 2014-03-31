<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ClientsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClientsController extends Zend_Controller_Action
{
    
    /**
     * message when client wasn't found
     */
    CONST MESSAGE_CLIENT_NOT_FOUND = 'Client %1$s not found';
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('detail', 'json')
                    ->addActionContext('changestate', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('addservice', 'json')
                    ->addActionContext('add', 'json')
                    ->addActionContext('addadditionalfield', 'json')
                    ->addActionContext('removeservice', 'json')
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $pager = Core_Model_DiFactory::getSearchClients();
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
    }
    
    public function detailAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            return $this->_forward('index');
        }
        
        $logManager = Core_Model_DiFactory::getLogManager();
        $servicesForm = new Core_Form_ClientServices();
        $form = new Core_Form_User;
        
        $this->_addAjaxUploads();
        
        $form->setAdditionalFieldsClient($client);
        if ($this->getRequest()->isPost()) {
            $values = $form->getValidValues($this->_request->getPost());
            if(!empty($values)) {
                $this->view->result = $clientManager->updateClient($client->getId(), $values);
            }
            if ($this->getParam("avatar") && !key_exists("avatar", $form->getMessages())){
                $this->_helper->json->sendJson(array("client" => $client->getData()));
            }
            
            $this->view->formErrors = $form->getMessages();
        }
        
        $data = $client->getData();
        
        $this->view->client = $data;
        $this->view->clientId = $client->getId();
        $this->view->logs = $logManager->getClientLogs($client->getId());
        $this->view->services = $client->getServices();
        $this->view->form = $form->setDefaults($data);
        
        $this->view->servicesForm = $servicesForm->setServiceSelect($client);
        $this->view->nodes = Core_Model_DiFactory::getModuleListings()
                ->getNodesWithDomainsByClient($client->getId());
        $this->view->domains = Core_Model_DiFactory::getModuleListings()
                ->getDomainsWithNodesByClient($client->getId());
        
        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $active["page"]->setLabel($client->getData("label"));
        }
    }
    
    public function addadditionalfieldAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            return;
        }
        
        $form = new Core_Form_AdditionalInfo();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->_request->getPost())) {
                $this->view->result = $clientManager
                        ->addAdditionalField($client->getId(), $form->getValues());
            } else {
                $this->view->formErrors = $form->getMessages(null, true);
            }
        }
    }

    public function addAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $form = new Core_Form_AddClient();
        $formAvatar = new Core_Form_Avatar;
        $avatar = $this->_request->getPost("avatar_tmp");
        $fileManager = Core_Model_DiFactory::getFileManager();

        if($this->_request->isPost()) {
            if ($formAvatar->isValid($this->_request->getPost())){
                $avatar = $fileManager->base64EncodeImage(
                          $fileManager->receiveHttpFileInfo("avatar", "tmp_name"));
                $this->_helper->json->sendJson(array("avatar" => $avatar));
            }else if($form->isValid($this->_request->getPost())) {
                $client = $clientManager->createClient($form->getValues());
                if ($client instanceof Core_Model_ValueObject_Client){
                    $client->setData(array("avatar" => $avatar))->save();
                    $this->notifyCreatedClient($client->getData());
                    $this->_helper->getHelper('Redirector')
                                  ->gotoRoute(array($client->getId(), $client->getLabel()), 'clientDetail');
                    return;
                }
                $avatar = $this->_request->getPost("avatar");
            }
            $this->view->formErrors = $formAvatar->getMessages();
        }

        $this->view->avatar = $avatar;
        $this->view->formAvatar = $formAvatar;
        $this->view->form = $form->setDefaults($this->_request->getPost());
    }

    public function deleteAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            return;
        }
        
        if(($status = $clientManager->deleteClient($client->getId()))) {
            $this->notifyDeletedClient($client->getData());
        }

        $this->view->status = $status;
    }

    public function changestateAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            return;
        }
        
        $clientManager->changeClientState($client->getId());
        
        $this->view->client = $client->getData();
    }
    
    protected function _addAjaxUploads()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $files = array();

        foreach ($adapter->getFileInfo() as $fileName => $file) {
            $files[$fileName] = $file['name'];
        }

        $this->_request->setPost($files);
    }
    
    public function addserviceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_CLIENT_NOT_FOUND, $this->getParam('clientLabel'));
            return;
        }
        
        $form = new Core_Form_ClientServices();
        $form->setServiceSelect($client);
        
        if($this->_request->getPost()) {
            if($form->isValid($this->_request->getPost())) {
                $this->view->status = $clientManager
                        ->addService($client->getId(), $form->getValue('service'));

                if(($service = Core_Model_DiFactory::getModuleRegistry()->getModule($form->getValue('service')))) {
                    if($service->getModuleConfig('routes/config/client/route')) {
                        $this->view->configClientRoute = $this->view->url(array($client->getId(), $client->getLabel()),
                            $service->getModuleConfig('routes/config/client/route'));
                    }
                    $this->view->service = $service->getModuleConfig();
                }
            }
            
            $this->view->formErrors = $form->getMessages();
        }
    }
    
    public function notifyDeletedClient($client)
    {
        $this->view->client = $client;

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setSubject("[Maze.dashboard] - A client has been deleted")
                     ->setBody($this->view->render("email/deleteuser.phtml"), true)
                     ->setToAdmins();

        if ($emailManager->send()){
            return true;
        }

        if ($emailManager->hasException()){
            Core_Model_DiFactory::getMessageManager()->addError(
                    $emailManager->getException()->getMessage());
        }

        return false;
    }

    public function notifyCreatedClient($client)
    {
        $this->view->client = $client;

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setSubject("[Maze.dashboard] - A new client has been created")
                     ->setBody($this->view->render("email/createduser.phtml"), true)
                     ->setToAdmins();

        if ($emailManager->send()){
            return true;
        }

        if ($emailManager->hasException()){
            Core_Model_DiFactory::getMessageManager()->addError(
                    $emailManager->getException()->getMessage());
        }

        return false;
    }

    public function removeserviceAction()
    {
        $clientId = $this->getParam("clientId");
        $serviceName = $this->getParam("serviceName");

        $this->view->result = Core_Model_DiFactory::getClientManager()->removeClientService($clientId, $serviceName);
    }

    public function switchtoclientAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest && $this->getParam('clientId', null)) {
            $clientManager = Core_Model_DiFactory::getClientManager();

            if(($client = $clientManager->getClient($this->getParam('clientId')))) {
                $clientData = $client->getData();
                $clientData['adminUser'] = Zend_Auth::getInstance()->getIdentity();
                Zend_Auth::getInstance()->getStorage()->write($clientData);
                
                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->goToRoute(array(), 'index');
                return;
            }
        }
        
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->goToRoute(array(), 'clients');
        return;
    }

}
