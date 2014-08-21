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
        $this->redirect("#/domains/edit/{$this->_getParam('domainId')}", array('code'=>301));
    }

    public function addAction()
    {
        $this->redirect("#/domains/new", array('code'=>301));
    }
}

