<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiDomainsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiDomainsController extends MazeLib_Rest_Controller
{

    /**
     * get paginated domains
     */
    public function getResourcesAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $result = array();

        if($page = $this->getParam('page')) {
            $result = $domainManager->paginate($this->getParam('limit', 10),
                $this->getParam('page', 1), $this->getParam('search', null));
        } elseif (($service = $this->getParam('service'))) {
            $result = $this->_arrayRemoveKeys($domainManager->getDomainsByServiceAsArray($service));
        } else {
            $result = $this->_arrayRemoveKeys($domainManager->getDomainsAsArray());
        }

        $this->_helper->json->sendJson($result);
    }

    public function postResourcesAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $form = new Core_Form_AddDomain();
        if ($form->isValid($this->getRequest()->getPost())) {
            $domainId = $domainManager->createDomain($form->getValue('name'),
                $form->getValue('owner'), $form->getValue('procurementplace'));

            if($domainId){
                $this->getResponse()->setHeader('Location', $this->view->url(array($domainId), 'domaindetail'));

                $response['result'] = true;
                $this->getResponse()->setHttpResponseCode(201);
            }
        } else {
            $this->_helper->json->sendJson(array("errors" => $form->getMessages()));
        }
    }

    public function deleteResourceAction()
    {
        if(!Core_Model_DiFactory::getDomainManager()->deleteDomain($this->getParam('domainId'))) {
            $this->_setServerErrorHeader();
        };

        $this->getResponse()->setBody(null);
    }

    public function postResourceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!$domainManager->getDomain($this->getParam("domainId"))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Domain();
        $params = $this->getAllParams();
        $response = array(
            'result' => false
        );

        $form->initDynamicContent($params);
        if($params && $form->isValidPartial($params) && ($values = $form->getValidValues($params))) {
            $response['result'] = $domainManager->updateDomain($this->getParam('domainId'), $values);
            $response['domain'] = $domainManager->getDomainForApi($this->getParam('domainId'));
        } else {
            $response['params'] = $params;
            $response['errForm'] = $form->getMessages();
        }

        if(!$response['result']) {
            $this->_setServerErrorHeader();
        }

        $this->_helper->json->sendJson($response);
    }

    public function putResourceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomain($this->getParam("domainId")))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_Domain();
        $form->setAdditionalFields($domain);

        $values = $form->getValidValues($this->getRequest()->getParams());
        if(!empty($values)) {
            if (($result = $domainManager->updateDomain($domain->getId(), $values))) {
                $this->getResponse()->setHttpResponseCode(202);
                $this->_helper->json->sendJson($domain->getData());
            }
        } else {
            $this->_helper->json->sendJson(array("errors" => $form->getMessages()));
        }
    }

    public function getResourceAction()
    {
        if(!($domain = Core_Model_DiFactory::getDomainManager()->getDomainForApi($this->getParam("domainId")))) {
            return $this->_setNotFoundHeader();
        }

        $this->_helper->json->sendJson($domain);
    }
}
