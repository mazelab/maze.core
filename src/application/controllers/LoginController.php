<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * LoginController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class LoginController extends Zend_Controller_Action
{

    protected $_redirector;
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }
    
    public function loginAction()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $form = new Core_Form_Login();
        $result = null;
        
        $this->_helper->layout()->setLayout('login');
        
        if($auth->hasIdentity()) {
            $this->_redirector->gotoRoute(array(), "index");
            return;
        } 
        
        if($this->_request->getPost()) {
            if($form->isValid($this->_request->getPost())) {
                $authAdapter = new MazeLib_Auth_Adapter($form->getValue('username'),
                                                         $form->getValue('password'));
                /*@var $result Zend_Auth_Result*/
                $authenticate = $auth->authenticate($authAdapter);   
                if($authenticate->isValid()) {
                    $identity = Zend_Auth::getInstance()->getIdentity();
                    
                    $this->_redirector->gotoRoute(array(), "index");
                    return;
                }
                $result = $authenticate->getCode();   
            }
            sleep(6);
        }
        
        $this->view->result = $result;
        $this->view->form = $form;
    }
	
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_redirector->gotoRoute(array(), "login");
        
        $this->_helper->layout()->setLayout('login');
    }

    public function forgotpasswordAction()
    {       
        $userManager = Core_Model_DiFactory::getUserManager();
        
        $this->_helper->layout()->setLayout('login');
        $form = new Core_Form_ResetPassword();
        $result = null;
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValidPartial($this->getRequest()->getPost()) &&
                    ($user = $userManager->getUserByEmail($form->getValue("userEmail")))) {
                $result = $userManager->setResetPasswordFlag($user->getData("email"));
                
                if (is_string($result)){
                    $result = $this->notifyuser($user, $result);
                }    
            }
        }

        $this->view->form = $form;
        $this->view->result = $result;
    }

    public function requestpasswordAction()
    {       
        $this->_helper->layout()->setLayout('login');
        $form = new Core_Form_ResetPassword();
        $result = null;

        if ($this->getRequest()->isPost()) {
            if ($form->isValidPartial($this->getRequest()->getPost())) {
                $userManager = Core_Model_DiFactory::getUserManager();
                $user = $userManager->getUserByEmail($form->getValue("userEmail"));

                if ($user){
                    $result = $userManager->setResetPasswordFlag($user->getData("email"));
                    if (is_string($result)){
                        $result = $this->notifyrequestpassword($user, $result);
                    }
                }else $result = false;
            }
        }

        $this->view->form = $form;
        $this->view->result = $result;
    }
    
    public function resetpasswordAction()
    {
        $token = $this->_request->getParam("token", null);

        $this->_helper->layout()->setLayout('login');
        $userManager = Core_Model_DiFactory::getUserManager();
        $form = new Core_Form_ResetPassword();
        $result = null;

        $userId = $userManager->getUserByResetPasswordTocken($token);     
         if ($userId && $this->_request->isPost()){
            $values = $form->getValidValues($this->_request->getPost());

            if($values){
                $result = $userManager->resetPassword($userId, $values);
            }
        }

        $this->view->token = $token;
        $this->view->tokenValid = ($userId ? true : false);
        $this->view->form = $form;
        $this->view->result = $result;
    }

    public function notifyuser(Core_Model_ValueObject_Interface_User $user, $token)
    {
        $this->view->token = $token;
        $this->view->username = $user->getUsername();

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setSubject("[Maze.dashboard] Password Reset")
                     ->setBody($this->view->render('email/forgotpassword.phtml'), true)
                     ->addTo($user->getEmail());

        if ($emailManager->send()){
            return true;
        }

        if ($emailManager->hasException()){
            Core_Model_DiFactory::getMessageManager()->addError(
                    $emailManager->getException()->getMessage());
        }

        return false;
    }

    public function notifyrequestpassword(Core_Model_ValueObject_Interface_User $user, $token)
    {
        $this->view->token = $token;
        $this->view->username = $user->getUsername();

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setSubject("[Maze.dashboard] Password Request")
                     ->setBody($this->view->render('email/requestpassword.phtml'), true)
                     ->addTo($user->getEmail());

        if ($emailManager->send()){
            return true;
        }

        if ($emailManager->hasException()){
            Core_Model_DiFactory::getMessageManager()->addError(
                    $emailManager->getException()->getMessage());
        }

        return false;
    }

    /**
     * set messages for view usage
     */
    public function postDispatch()
    {
        $this->view->assign(Core_Model_DiFactory::getMessageManager()->getMessages());
    }

}

