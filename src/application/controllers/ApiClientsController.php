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

}
