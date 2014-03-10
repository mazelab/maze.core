<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * DashboardController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class DashboardController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();

        if (isset($identity['group']) && $identity['group'] == Core_Model_UserManager::GROUP_ADMIN) {
            $this->forward('dashboardadmin');
            return;
        }

        $this->forward('dashboardclient');
        return;
    }

    public function dashboardadminAction()
    {
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $clientManager = Core_Model_DiFactory::getClientManager();
        $nodeManager = Core_Model_DiFactory::getNodeManager();
        $logManager = Core_Model_DiFactory::getLogManager();
        $newsManager = Core_Model_DiFactory::getNewsManager();

        $this->view->conflicts = $logManager->getAggregatedConflicts();
        $this->view->log = $logManager->getLogs();
        
        $this->view->nodes = $nodeManager->getNodesAsArray();
        $this->view->domains = $domainManager->getDomainsAsArray();
        $this->view->clients = $clientManager->getClientsAsArray();
        $this->view->lastNodes = $nodeManager->getLastModifiedNodesAsArray();
        $this->view->lastClients = $clientManager->getClientsByLastModificationAsArray();
        $this->view->lastDomains = $domainManager->getDomainsByModificationDateAsArray();
        $this->view->lastNews = $newsManager->getMessages(4, Core_Model_NewsManager::STATUS_PUBLIC);
    }

    public function dashboardclientAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        $domainManager = Core_Model_DiFactory::getDomainManager();
        $clientManager = Core_Model_DiFactory::getClientManager();
        $logManager = Core_Model_DiFactory::getLogManager();
        $newsManager = Core_Model_DiFactory::getNewsManager();
        
        $this->view->conflicts = $logManager->getClientContextLogs($identity['_id'],
                Core_Model_Logger::TYPE_CONFLICT);
        
        $this->view->client = $clientManager->getClientAsArray($identity['_id']);
        $this->view->domains = $domainManager->getDomainsByOwnerAsArray($identity['_id']);
        $this->view->modules = $clientManager->getClientServices($identity['_id']);
        $this->view->log = $logManager->getClientLogs($identity['_id']);
        $this->view->lastNews = $newsManager->getMessages(4, Core_Model_NewsManager::STATUS_PUBLIC);
    }

}

