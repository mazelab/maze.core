<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ProfileController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ProfileController extends Zend_Controller_Action
{

    /**
     * @var array
     */
    protected $_identity;
    
/**
     * message when password changing has failed
     */
    CONST MESSAGE_PASSWORD_SET_FAILED = 'The password could not be set';
    
    /**
     * message when password changing was successful
     */
    CONST MESSAGE_PASSWORD_SET_SUCCESS = 'The password has been changed';

    public function init()
    {
        if ($this->_request->isXmlHttpRequest()
                && !$this->_request->getParam('format', false)){
            $this->_setParam('format', 'html');
        }

        $this->_identity = Zend_Auth::getInstance()->getIdentity();
        if (isset($this->_identity['adminUser']['group'])){
            $this->_identity = $this->_identity['adminUser'];
        }
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('profileclient', array('json', 'html'))
                    ->addActionContext('accessclient', array('json', 'html'))
                    ->addActionContext('profileadmin', array('json'))
                    ->addActionContext('accessadmin', array('json'))
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->layout()->disableLayout();
    }

    protected function _addAjaxUploads()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $files = array();

        foreach ($adapter->getFileInfo() as $fileName => $file) {
            $files[$fileName] = $file['name'];
        }

        $this->_request->setPost($files);
    }
    
    protected function _processAccessView()
    {
        $form = new Core_Form_Access();
        $form->addPasswordComparison($this->_identity['_id']);
        $userManager = Core_Model_DiFactory::getUserManager();
        $userData = $userManager->getUserAsArray($this->_identity['_id']);
 
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($this->getRequest()->getPost())) {
                $this->view->formErrors = $form->getMessages();
                return false;
            }

            if (($result = $userManager->updateUserPassword($this->_identity['_id']
                         , $form->getValidValues($form->getValues())))) {
                // update Auth identity
                $userData = $userManager->getUserAsArray($this->_identity['_id']);
                Zend_Auth::getInstance()->getStorage()->write($userData);
                $this->view->result = $result;
                Core_Model_DiFactory::getMessageManager()
                        ->addSuccess(self::MESSAGE_PASSWORD_SET_SUCCESS);
            } else {
                Core_Model_DiFactory::getMessageManager()
                        ->addError(self::MESSAGE_PASSWORD_SET_FAILED);
            }
        }

        $this->view->form = $form->setDefaults($userData);
    }

    public function indexAction()
    {
        if (isset($this->_identity['group']) && $this->_identity['group'] == Core_Model_UserManager::GROUP_ADMIN) {
            $this->forward('profileadmin');
            return;
        }

        $this->forward('profileclient');
        return;
    }

    public function profileadminAction()
    {
        $adminManager = Core_Model_DiFactory::getAdminManager();
        $form = new Core_Form_User();
        $config = Core_Model_DiFactory::getConfig();
        
        $this->_addAjaxUploads();

        if ($this->_request->isPost()) {
            if ($this->_request->getPost("config")){
                $this->view->result = $config->setData($form->getValidValues($this->_request->getPost("config")))->save();
            }else{
                $values = $form->getValidValues($this->_request->getPost());

                if (!empty($values)) {
                    $result = $adminManager->updateAdmin($this->_identity['_id'],
                                    $form->getValidValues($form->getValues()));

                    $admin = $adminManager->getAdminAsArray($this->_identity['_id']);
                    Zend_Auth::getInstance()->getStorage()->write($admin);
                    $this->view->identity = $admin;
                    $this->_helper->json->sendJson(array("identity" => $admin, "result" => $result));
    }
            }
            $this->view->formErrors = $form->getMessages(null, true);
        }

        if (!isset($admin))
            $admin = $adminManager->getAdminAsArray($this->_identity['_id']);
        
        $this->view->form = $form->setDefaults($admin);
        $this->view->admin = $admin;
    }

    public function profileclientAction()
    {
        $clientManager = Core_Model_DiFactory::getClientManager();
        $form = new Core_Form_User();
        $result = false;
        $clientData = $clientManager->getClientAsArray($this->_identity['_id']);
        $this->_addAjaxUploads();

        if ($this->_request->isPost()) {
            $values = $form->getValidValues($this->_request->getPost());
            
            if (!empty($values)) {
                $result = $clientManager->updateClient($this->_identity['_id'], $values);
                $clientData = $clientManager->getClientAsArray($this->_identity['_id']);
                Zend_Auth::getInstance()->getStorage()->write($clientData);
                
                $this->view->identity = $clientData;
            }

            $this->view->formErrors = $form->getMessages();
        }

        if ($this->getParam("avatar") && !array_key_exists("avatar", $form->getMessages())){
            $this->_helper->json->sendJson(array("client" => $clientData));
        }
        
        $this->view->result = $result;
        $this->view->client = $clientData;
        $this->view->form = $form->setDefaults($clientData);
    }

    public function accessAction()
    {
        if (isset($this->_identity['group']) && $this->_identity['group'] == Core_Model_UserManager::GROUP_ADMIN) {
            return $this->forward('accessadmin');
        }

        return $this->forward('accessclient');
    }
    
    public function accessadminAction()
    {
        $this->_processAccessView();
    }
    
    public function accessclientAction()
    {
        $this->_processAccessView();
    }

    /**
     * set messages for view usage
     */
    public function postDispatch()
    {
        $this->view->assign(Core_Model_DiFactory::getMessageManager()->getMessages());
    }
    
}
