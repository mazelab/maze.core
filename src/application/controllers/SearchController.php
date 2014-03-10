<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * SearchController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class SearchController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('clients', 'html')
                    ->addActionContext('domains', 'html')
                    ->addActionContext('nodes', 'html')
                    ->initContext();
    }
    
    protected function indexAction()
    {
        $search = Core_Model_DiFactory::getSearchAll();
        $search->setLimit($this->getParam('limit', 10))
               ->setSearchTerm($this->getParam('term'));

        if($this->getParam('pagerAction') == 'last') {
            $search->last();
        } else {
            $search->page($this->getParam('page', 1));
        }
        
        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $label = $this->view->translate('Search results');
            $label .= ': ' . $search->getSearchTerm();
            
            $active["page"]->setLabel($label);
        }
        
        $this->view->pager = $search->asArray();
        $this->view->addBasePath(APPLICATION_PATH . '/layouts');
    }
    
    protected function indexingAction()
    {
        Core_Model_DiFactory::getIndexManager()->setIndexes();
        
        $redirector = $this->_helper->getHelper('redirector');
        $redirector->goToRoute(array(), 'index');
    }
    
}

