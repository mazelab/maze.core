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
        $jsonDomains = array();

        if(($service = $this->getParam('service'))) {
            $domains = $domainManager->getDomainsByServiceAsArray($service);
        } else {
            $domains = $domainManager->getDomainsAsArray();
        }

        foreach($domains as $domain) {
            array_push($jsonDomains, $domain);
        }

        $this->_helper->json->sendJson($jsonDomains);
    }

    public function postResourcesAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $form = new Core_Form_AddDomain();
        if ($form->isValid($this->getRequest()->getPost())) {
            $domainId = $domainManager->createDomain($form->getValue('name'),
                $form->getValue('owner'), $form->getValue('procurementplace'));

            if($domainId) {
                $this->getResponse()->setHttpResponseCode(201);
            }
        } else {
            $this->_helper->json->sendJson(array("errors" => $form->getMessages()));
        }
    }

    public function deleteResourceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(($domain = $domainManager->getDomain($this->getParam("domainId"))) &&
            $domainManager->deleteDomain($domain->getId())) {
                $this->_setAcceptedHeader();
        }
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
        $domainManager = Core_Model_DiFactory::getDomainManager();
        if(!($domain = $domainManager->getDomain($this->getParam("domainId")))) {
            return $this->_setNotFoundHeader();
        }

        $domainData = $domain->getData();

        if ($this->getParam("client") == true) {
            $clientManager = Core_Model_DiFactory::getClientManager();
            $domainData["client"] = $clientManager->getClientByDomainAsArray($domain->getId());
            $domainData["client"]["uri"] = $this->view->url(array($domainData["owner"]), "api_client");
        }
        if ($this->getParam("nodes") == true) {
            $moduleListings = Core_Model_DiFactory::getModuleListings();
            $domainData["nodes"] = $moduleListings->getNodesWithServicesByDomain($domain->getId());
        }
        if ($this->getParam("logs") == true) {
            $domainData["logs"] = Core_Model_DiFactory::getLogManager()->getDomainLogs($domain->getId());
        }
        if ($this->getParam("services") == true) {
            $domainData["services"] = $domain->getServices();
        }

        $this->_helper->json->sendJson($domainData);
    }
}
