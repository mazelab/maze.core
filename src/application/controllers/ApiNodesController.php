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
     * message when api request for node was not found
     */
    CONST MESSAGE_API_REQUEST_NOT_FOUND = 'Api request for %1$s not found';

    /**
     * get nodes
     */
    public function getResourcesAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $result = array();

        if(($domain = $this->getParam('domain'))) {
            $result = $nodeManager->getNodesByDomainForApi($domain);
        } elseif(($service = $this->getParam('service'))) {
            $result = $this->_arrayRemoveKeys($nodeManager->getNodesByServiceAsArray($service));
        } else {
            $result = $this->_arrayRemoveKeys($nodeManager->getNodesAsArray());
        }

        $this->_helper->json->sendJson($result);
    }

    public function postResourcesAction()
    {
        $apiManager = Core_Model_DiFactory::getApiManager();
        if(!($request = $apiManager->getUnregisteredApiRequest($this->getParam('name'))) ||
                !array_key_exists('data', $request)) {
            $messageManager = Core_Model_DiFactory::getMessageManager();
            $messageManager->addError(self::MESSAGE_API_REQUEST_NOT_FOUND, $this->getParam('name'));
            return $this->_helper->json->sendJson($messageManager->getMessages());
        }

        $form = new Core_Form_AddNode();
        if($form->isValid($this->getRequest()->getPost())) {
            $nodeManager = Core_Model_DiFactory::getNodeManager();
            if($nodeManager->createNode($form->getValues())){
                $this->getResponse()->setHttpResponseCode(201);
            }
        }
    }

    public function getResourceAction()
    {
        if(!($node = Core_Model_DiFactory::getNodeManager()->getNodeForApi($this->getParam("nodeId")))) {
            return $this->_setNotFoundHeader();
        }

        $this->_helper->json->sendJson($node);
    }

    public function putResourceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!($node = $nodeManager->getNode($this->getParam("nodeId")))) {
            $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Node();
        $form->setAdditionalFields($node);

        if (($values = $form->getValidValues($this->getRequest()->getParams()))) {
            if ($nodeManager->updateNode($node->getId(), $values)) {
                $this->getResponse()->setHttpResponseCode(200);
                $this->_helper->json->sendJson($nodeManager->getNodeForApi($this->getParam("nodeId")));
            }
        }
    }

    public function postResourceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!$nodeManager->getNode($this->getParam("nodeId"))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Node();
        $params = $this->getAllParams();
        $response = array(
            'result' => false
        );

        $form->initDynamicContent($params);

        if($params && $form->isValidPartial($params) && ($values = $form->getValidValues($params))) {
            $response['result'] = $nodeManager->updateNode($this->getParam('nodeId'), $values);
            $response['node'] = $nodeManager->getNodeForApi($this->getParam('nodeId'));
        } else {
            $response['params'] = $params;
            $response['errForm'] = $form->getMessages();
        }

        if(!$response['result']) {
            $this->_setServerErrorHeader();
        }

        $this->_helper->json->sendJson($response);
    }

    public function deleteResourceAction()
    {
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        if(!$nodeManager->deleteNode($this->getParam('nodeId'))) {
            $this->_setServerErrorHeader();
        };

        $this->getResponse()->setBody(null);
    }

}
