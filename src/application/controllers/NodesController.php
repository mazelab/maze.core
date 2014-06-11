<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * NodesController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class NodesController extends Zend_Controller_Action
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
        $url = $this->view->url(array(), 'nodes') . "/#/edit/{$this->_getParam('nodeId')}";

        return $this->redirect($url, array('code'=>301));
    }

    public function registerapiAction()
    {
        $url = $this->view->url(array(), 'nodes') . "/#/register/{$this->_getParam('nodeName')}";

        return $this->redirect($url, array('code'=>301));
    }
}

