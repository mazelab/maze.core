<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * DomainsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class DomainsController extends Zend_Controller_Action
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
        $url = $this->view->url(array(), 'domains') . "/#/edit/{$this->_getParam('domainId')}";

        return $this->redirect($url, array('code'=>301));
    }

    public function addAction()
    {
        $url = $this->view->url(array(), 'domains') . "/#/new";

        return $this->redirect($url, array('code'=>301));
    }

}

