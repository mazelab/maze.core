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
        $this->redirect("#/clients/edit/{$this->_getParam('clientId')}", array('code'=>301));
    }
    
    public function addAction()
    {
        $this->redirect("#/clients/new", array('code'=>301));
    }

}
