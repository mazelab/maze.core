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

    public function deleteResourceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();

        if(($domain = $domainManager->getDomainByName($this->_getParam("domainName")))) {
            if ($domainManager->deleteDomain($domain->getId())) {
                $this->getResponse()->setHttpResponseCode(200);
            } else {
                $this->_setAcceptedHeader();
            }
        } else {
            $this->_setNoContentHeader();
        }
    }

    public function putResourceAction()
    {
        $response = new stdClass();

        $domainManager = Core_Model_DiFactory::getDomainManager();
        $domainName = $this->_getParam("domainName");
        if(($domain = $domainManager->getDomainByName($domainName))) {
            $this->getResponse()->setHttpResponseCode(202);
            return;
        }

        $form = new Core_Form_AddDomain();
        if ($form->isValid($this->getRequest()->getParams())) {
            $domainId = $domainManager->createDomain($form->getValue('name'),
                $form->getValue('owner'), $form->getValue('procurementplace'));

            if($domainId) {
                $response->domainId = $domainId;
                $response->uri = $this->view->url(array(), 'domains');
                $this->getResponse()->setHttpResponseCode(201);
            }
            $response->result = $domainId === false ? false : true;
        } else {
            $response->errors = $form->getMessages();
        }

        $this->_helper->json->sendJson($response);
    }

    public function getResourceAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $domainName = $this->_getParam("domainName");

        if(!($domain = $domainManager->getDomainByName($domainName))) {
            $this->_helper->json->sendJson(Core_Model_DiFactory::getMessageManager()->getMessages());
            return;
        }

        $this->_helper->json->sendJson($domain->getData());
    }

    public function postResourceAction()
    {
        $response = new stdClass();
        $domainManager = Core_Model_DiFactory::getDomainManager();

        $domainName = $this->_getParam("domainName");
        if(($domain = $domainManager->getDomainByName($domainName))) {

            $form = new Core_Form_Domain();
            $form->setAdditionalFields($domain);

            $values = $form->getValidValues($this->getRequest()->getPost());
            if(!empty($values)) {
                if (($result = $domainManager->updateDomain($domain->getId(), $values))) {
                    $response = (object) $domain->getData();
                }
            } else {
                $response->errors = $form->getMessages();
            }

        }

        $this->_helper->json->sendJson($response);
    }
}