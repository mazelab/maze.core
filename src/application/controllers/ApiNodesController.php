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

    /**
     * get nodes
     */
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

}
