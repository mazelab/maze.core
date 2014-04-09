<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * SystemController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class SystemController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->getHelper('AjaxContext')
                      ->addActionContext('index', 'json')
                      ->addActionContext('admins', 'html')
                      ->addActionContext('testmail', 'json')
                      ->addActionContext('database', 'json')
                      ->addActionContext('changeadminstate', 'json')
                      ->addActionContext('deleteadmin', 'json')
                      ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $config = Core_Model_DiFactory::getConfig();
        $configData = $config->getData();
        $form = new Core_Form_System;

        if (Zend_Registry::get('config')->mongodb instanceof Zend_Config){
            $configData = array_merge($configData, Zend_Registry::get('config')->toArray());
        }

        $form->setDefaults($configData);

        if ($this->getRequest()->isPost()){
            if (!$form->isValidPartial($this->getRequest()->getPost())){
                $this->view->formErrors = $form->getMessages();
            }else {
                $values = $form->getValidValues($this->getRequest()->getPost());
                $this->view->result = $config->setData($values)->save();
                $this->view->update = $values;
            }
        }

        $this->view->addBasePath(APPLICATION_PATH . '/layouts');
        $this->view->form = $form;

        $this->adminsAction();
    }

    public function databaseAction()
    {
        $dbSetting = $this->getRequest()->getPost("dbSetting");
        $installManager = Core_Model_DiFactory::getInstallManager();
        $formDatabase = new Core_Form_Database;

        if (array_key_exists("password", $dbSetting)) {
            $dbSetting["dbPassword"] = $dbSetting["password"];
        }
        if (array_key_exists("username", $dbSetting)) {
            $dbSetting["dbUsername"] = $dbSetting["username"];
        }

        if ($formDatabase->setDefaults($dbSetting) && $installManager->validateAndAddToConfig($formDatabase)) {
            $this->view->result = true;
        }

        $this->view->formErrors = $formDatabase->getMessages();
    }

    public function adminsAction()
    {
        $pager = Core_Model_DiFactory::getSearchAdmins();
        $pager->setLimit($this->getParam("limit", 10));

        if($this->getParam('term')) {
            $pager->setSearchTerm($this->getParam('term'));
        }

        $action = $this->getParam('pagerAction');
        if($action == 'last') {
            $pager->last();
        } else {
            $pager->setPage($this->getParam('page', 1))->page();
        }

        $this->view->addBasePath(APPLICATION_PATH . '/layouts');
        $this->view->pager = $pager->asArray();
    }

    public function switchtoadminAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();

        if(isset($identity['adminUser']) && is_array($identity['adminUser'])) {
            Zend_Auth::getInstance()->getStorage()->write($identity['adminUser']);
        }

        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->goToRoute(array(), 'clients');
    }

    public function addadminAction()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        $form = new Core_Form_AddAdmin();

        if($this->_request->getPost()) {

            if($form->isValid($this->_request->getPost()) &&
                $adminManager->createAdmin($form->getValues())) {
                $this->redirect($this->view->url(array(), "system"));
            }
        }

        $this->view->form = $form;  
    }

    public function deleteadminAction()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        
        $status = false;
        if(($admin = $adminManager->getAdminByUserName($this->getParam('userName')))) {
            $status = $adminManager->deleteAdmin($admin->getId());
        }
        
        $this->view->status = $status;
    }

    public function changeadminstateAction()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        
        if(($admin = $adminManager->getAdminByUserName($this->getParam('userName')))) {
            $adminManager->changeAdminState($admin->getId());
            $this->view->admin = $admin->getData();
        }
    }

    public function testmailAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setOptions(array(
            "to"      => array($identity["email"]),
            "subject" => "[Maze.dashboard] Test",
            "body"    => "ok"
        ));
        if ($emailManager->send()){
            $this->view->result = true;
        }else if ($emailManager->hasException()){
            $this->view->exception = $emailManager->getException()->getMessage();
        }
    }
}
