<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiClientsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiClientsController extends MazeLib_Rest_Controller
{

    protected  function _notifyDeletedClient(array $client)
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

    protected function _notifyCreatedClient(array $client)
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

    /**
     * get clients
     */
    public function getResourcesAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $result = array();

        if(($service = $this->getParam('service'))) {
            $result = $this->_arrayRemoveKeys($clientManager->getClientsByServiceAsArray($service));
        } elseif(($node = $this->getParam('node'))) {
            $result = $clientManager->getClientsByNodeForApi($node);
        } elseif($page = $this->getParam('page')) {
            $result = $clientManager->paginate($this->getParam('limit', 10), $this->getParam('page', 1),
                $this->getParam('search', null));
        } else{
            $result = $this->_arrayRemoveKeys($clientManager->getClientsAsArray());
        }

        $this->_helper->json->sendJson($result);
    }

    public function getResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!$clientManager->getClient($this->getParam("clientId"))) {
            return $this->_setNotFoundHeader();
        }

        $this->_helper->json->sendJson($clientManager->getClientForApi($this->getParam('clientId')));
    }

    public function putResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam("clientId")))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_User;
        $form->setAdditionalFieldsClient($client);

        if ($this->getParam("changeState")) {
            $clientManager->changeClientState($client->getId());
        }

        $values = $form->getValidValues($this->getRequest()->getParams());
        if($clientManager->updateClient($client->getId(), $values)) {
            $this->getResponse()->setHttpResponseCode(202);
        }

        $this->_helper->json->sendJson($client->getData());
    }

    public function postResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!$clientManager->getClient($this->getParam("clientId"))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Client();
        $params = $this->getAllParams();
        $response = array(
            'result' => false
        );

        $form->initDynamicContent($params);
        if($params && $form->isValidPartial($params) && ($values = $form->getValidValues($params))) {
            $response['result'] = $clientManager->updateClient($this->getParam('clientId'), $values);
            $response['client'] = $clientManager->getClientForApi($this->getParam('clientId'));
        } else {
            $response['params'] = $params;
            $response['errForm'] = $form->getMessages();
        }

        if(!$response['result']) {
            $this->_setServerErrorHeader();
        }

        $this->_helper->json->sendJson($response);
    }

    public function postResourcesAction()
    {
        $clientsManager = Core_Model_DiFactory::getClientManager();
        $form = new Core_Form_Client();

        if ($form->isValid($this->getRequest()->getPost())) {
            if(($client = $clientsManager->createClient($form->getValues()))){
                $this->_notifyCreatedClient($client->getData());

                $this->getResponse()->setHeader('Location', $this->view->
                    url(array($client->getId(), $client->getLabel()), 'clientDetail'));

                $response['result'] = true;
                $this->getResponse()->setHttpResponseCode(201);
            }
        } else {
            $this->_setServerErrorHeader();
            $this->_helper->json->sendJson(array("formErrors" => $form->getMessages()));
        }
    }

    public function deleteResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            return $this->_setServerErrorHeader();
        }

        if(($status = $clientManager->deleteClient($client->getId()))) {
            $this->_notifyDeletedClient($client->getData());
        }

        $this->getResponse()->setBody(null);
    }

}
