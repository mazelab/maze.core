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

    public function indexAction()
    {
    }

    public function detailAction()
    {
        $this->redirect("#/nodes/edit/{$this->_getParam('nodeId')}", array('code'=>301));
    }

    public function registerapiAction()
    {
        $this->redirect("#/nodes/register/{$this->_getParam('nodeName')}", array('code'=>301));
    }
}

