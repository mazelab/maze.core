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

    public function getResourceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNode($this->getParam("nodeId")))) {
            return $this->_setNotFoundHeader();
        }

        $this->_helper->json->sendJson($node->getData());
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
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNode($this->getParam("nodeId")))) {
            $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Node();
        $form->setAdditionalFields($node);

        $values = $form->getValidValues($this->getRequest()->getParams());
        if (!empty($values)) {
            if ($nodeManager->updateNode($node->getId(), $values)) {
                $this->getResponse()->setHttpResponseCode(202);
                $this->_helper->json->sendJson($node->getData());
            }
        }
    }

    public function deleteResourceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(($node = $nodeManager->getNode($this->getParam("nodeId"))) &&
            $nodeManager->deleteNode($node->getId())) {
                $this->_setAcceptedHeader();
        }
    }

}
