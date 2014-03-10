<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Controller_Action_Helper_SetDefaultViewVars
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Controller_Action_Helper_SetDefaultViewVars extends Zend_Controller_Action_Helper_Abstract
{
    public $view = null;

    public function __construct()
    {
        $this->view = Zend_Layout::getMvcInstance()->getView();
    }

    public function postDispatch()
    {        
        $this->view->assign(Core_Model_DiFactory::getMessageManager()->getMessages());

        if (file_exists(APPLICATION_PATH . '/../data/configs/server.ini') &&
                Zend_Registry::getInstance()->isRegistered("mazeConfig")){
            $mazeConfig = Zend_Registry::getInstance()->get("mazeConfig");
            $this->view->assign("company", (string) $mazeConfig->getData("company"));
        }
    }

}