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

    /**
     * get clients
     */
    public function getResourcesAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $jsonClients = array();

        if(($service = $this->getParam('service'))) {
            $clients = $clientManager->getClientsByServiceAsArray($service);
        } else {
            $clients = $clientManager->getClientsAsArray();
        }

        foreach($clients as $client) {
            array_push($jsonClients, $client);
        }

        $this->_helper->json->sendJson($jsonClients);
    }

    public function getResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam("clientId")))) {
            return $this->_setNotFoundHeader();
        }

        $this->_helper->json->sendJson($client->getData());
    }

    public function headResourceAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function postResourcesAction()
    {
        $this->_setMethodNotAllowedHeader();
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

    public function deleteResourceAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(($client = $clientManager->getClient($this->getParam("clientId"))) &&
            $clientManager->deleteClient($client->getId())) {
            $this->_setAcceptedHeader();
        }
    }

}
