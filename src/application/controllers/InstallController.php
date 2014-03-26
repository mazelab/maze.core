<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * InstallController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class InstallController extends Zend_Controller_Action
{

    protected $_redirector;

    /**
     * message when installation failed
     */
    CONST MESSAGE_INSTALLATION_FAILED = "the installation could not be completed";
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_helper->layout()->setLayout('install');

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
    }

    public function indexAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();
        
        if (!$installManager->isInstalled()) {
            $this->_forward("language");
        }
    }

    public function languageAction()
    {
        $form = new Core_Form_Languages;
        $installManager = Core_Model_DiFactory::getInstallManager();

        if ($this->_request->isPost()){
            if ($form->isValid($this->_request->getPost()) &&  $installManager->validateAndAddToConfig($form)){
                $this->_redirector->gotoRoute(array(), "installDatabase");
                $this->_redirector->redirect();
                return;
            }
        }

        $this->view->form = $form;
    }

    public function databaseAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();
        $form = new Core_Form_Database;

        if ($this->_request->getPost()) {
            $form->setDefaults($this->_request->getPost());
            if ($installManager->validateAndAddToConfig($form)) {

                if (!$installManager->isDbConnection()) {
                    Core_Model_DiFactory::getMessageManager()
                        ->addError("Error establishing a database connection");
                } else {
                    $this->_redirector->gotoRoute(array(), "installAdminuser");
                    $this->_redirector->redirect();
                    return;
                }
            }
        }
        
        $this->view->form = $form->setDefaults($installManager->getConfig()->toArray());
    }

    public function adminuserAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();
        $form = new Core_Form_Adminuser;

        if ($this->_request->getPost()) {
            $form->setDefaults($this->_request->getPost());
            if ($installManager->validateAndAddToConfig($form)) {
                $this->_redirector->gotoRoute(array(), "installOverview");
                $this->_redirector->redirect();
                return;
            }
        }

        $this->view->form = $form->setDefaults($installManager->getConfig()->toArray());
        $this->view->addBasePath(realpath($this->getFrontController()
            ->getControllerDirectory('core') . '/../views'));
    }

    public function overviewAction()
    {        
        if (empty($this->view->routeComplete))
            $this->view->routeComplete = $this->view->url(array(), 'installComplete');

        $installManager = Core_Model_DiFactory::getInstallManager();
        $installManager->registerSecurekey();

        $this->view->config = $installManager->getConfig()->toArray();
    }

    public function completeAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();

        if (!$installManager->install()) {
            $this->forward('failed');
            return;
        }
    }

    public function reconfigureAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();
        $identity    = Zend_Auth::getInstance()->getIdentity();
        $formConfig  = new Core_Form_Reconfigure();
        $mazeconfig  = Core_Model_DiFactory::getConfig();
        $reconfigure = array_merge(array(
            "email"         => $identity["email"],
            "username"      => $identity["username"],
            "password"      => $identity["password"],
            "passwordRepeat"=> $identity["password"],
            "company"       => $mazeconfig->getData("company"),
            "language"      => Zend_Locale::findLocale()
        ), $installManager->getConfig()->toArray());

        if ($installManager->validateAndAddToConfig($formConfig->setDefaults($reconfigure))){
            $this->view->config = $installManager->getConfig()->toArray();
            $this->view->routeComplete = $this->view->url(array(), 'reinstallComplete');
        }else {
            Core_Model_DiFactory::getMessageManager()->addError(self::MESSAGE_INSTALLATION_FAILED);
            $this->view->reconfigInvalid = true;
        }

        $this->_forward("overview");
    }

    public function reinstallcompleteAction()
    {
        $installManager = Core_Model_DiFactory::getInstallManager();
        $reinstall = $installManager->reinstall();

        Zend_Auth::getInstance()->clearIdentity();

        if (!$reinstall) {
            $this->forward('failed');
            return;
        }
    }

    public function failedAction()
    {
        
    }

}

