<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ClientsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClientsController extends Zend_Controller_Action
{
    
    public function init()
    {
        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
    }
    
    public function detailAction()
    {
        $url = $this->view->url(array(), 'clients') . "/#/edit/{$this->_getParam('clientId')}";

        return $this->redirect($url, array('code'=>301));
    }
    
    public function addAction()
    {
        $url = $this->view->url(array(), 'clients') . "/#/new";

        return $this->redirect($url, array('code'=>301));
    }

}
