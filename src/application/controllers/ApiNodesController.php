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

    public function indexAction()
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

    public function getAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function headAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function postAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function putAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function deleteAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

}
