<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiAuthController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiAuthController extends MazeLib_Rest_Controller
{

    public function postClientAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        if(!($client = $clientManager->getClient($this->getParam('clientId')))) {
            return $this->_setNotFoundHeader();
        }

        $clientData = $client->getData();
        $clientData['adminUser'] = Zend_Auth::getInstance()->getIdentity();
        Zend_Auth::getInstance()->getStorage()->write($clientData);

        $this->getResponse()->setHeader('Location', $this->view->url(array(), 'index'));
    }

}
