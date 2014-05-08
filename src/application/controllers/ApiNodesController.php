<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiNodesController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiNodesController extends MazeLib_Rest_Controller
{

    public function getResourcesAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $jsonNodes = array();

        if(($service = $this->getParam('service'))) {
            $nodes = $nodeManager->getNodesByServiceAsArray($service);
        } else {
            $nodes = $nodeManager->getNodesAsArray();
        }

        foreach($nodes as $node) {
            array_push($jsonNodes, $node);
        }

        $this->_helper->json->sendJson($jsonNodes);
    }

    public function getResourceAction()
    {
        $this->_setMethodNotAllowedHeader();
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
        $this->_setMethodNotAllowedHeader();
    }

    public function deleteResourceAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

}
