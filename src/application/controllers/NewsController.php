<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * NewsController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class NewsController extends Zend_Controller_Action
{
    /**
     * message when news wasn't found
     */
    CONST MESSAGE_NEWS_NOT_FOUND = "Message not found";

    /**
     * message where news published and sent to all clients
     */
    CONST MESSAGE_PUBLISHED_AND_SENT = "Message published and sent to all clients";

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                ->addActionContext('detailadmin', 'json')
                ->addActionContext('tags', 'json')
                ->addActionContext('index', 'html')
                ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
        $this->_helper->layout()->disableLayout();
    }

    public function indexAction()
    {
        $pager = Core_Model_DiFactory::getSearchNews();
        $pager->setLimit($this->getParam("limit", 10));

        if($this->getParam("term")){
            $pager->setSearchTerm($this->getParam("term"));
        }

        $action = $this->getParam("pagerAction");
        if($action == "last") {
            $pager->last();
        } else {
            $pager->setPage($this->getParam("page", 1))->page();
        }

        $this->view->pager = $pager->asArray();
        $this->view->addBasePath(APPLICATION_PATH . "/layouts");
    }

    public function addAction()
    {
        $form = new Core_Form_AddNews;
        $newsManager = Core_Model_DiFactory::getNewsManager();

        if($this->getRequest()->isPost()) {
            $form->setTagFields($this->getRequest()->getPost())
                 ->addTagsFields($this->getRequest()->getPost("tags"));
            if($form->isValid($this->getRequest()->getPost())) {
                if ($newsManager->createMessage($form->getValues())){
                    $this->redirect("news");
                }
            }
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $newsManager = Core_Model_DiFactory::getNewsManager();
        $messageId   = $this->getParam("newsId");

        if ($this->getRequest()->isPost() && $messageId){
            $this->view->result = $newsManager->deleteMessage($messageId);
        }
    }

    public function detailAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();

        if (isset($identity["group"]) && $identity["group"] == Core_Model_UserManager::GROUP_ADMIN) {
            $this->forward("detailadmin");
            return;
        }

        $this->forward("detailclient");
        return;
    }

    public function detailadminAction()
    {
        $newsManager = Core_Model_DiFactory::getNewsManager();
        $newsId     = $this->getParam("newsId");
        $form       = new Core_Form_News;

        if (!($message = $newsManager->getMessage($newsId))){
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NEWS_NOT_FOUND);
            return $this->_forward("index");
        }

        if ($this->getRequest()->isPost()){
            $postdata = $this->getRequest()->getPost();

            if ($form->isValidPartial($postdata) && $newsManager->updateMessage($newsId, $postdata)){
                $this->view->result = true;

                if (array_key_exists("sentEmail", $postdata) && isset($postdata["status"]) &
                        $postdata["status"] == Core_Model_NewsManager::STATUS_PUBLIC){
                    if ($this->emailSendMessage($newsId)){
                        Core_Model_DiFactory::getMessageManager()
                                ->addNotification(self::MESSAGE_PUBLISHED_AND_SENT);
                    }
                    $this->view->result = $this->emailSendMessage($newsId);
                }
            }
            $this->view->formErrors = $form->getMessages();
        }

        $this->view->message = $message;            
        $this->view->form    = $form->setTagFields($message)->setDefaults($message);
    }

    public function detailclientAction()
    {
        $newsManager = Core_Model_DiFactory::getNewsManager();

        if (!($message = $newsManager->getMessage($this->getParam("newsId")
                ,Core_Model_NewsManager::STATUS_PUBLIC))){
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_NEWS_NOT_FOUND);
            return $this->_forward("index", "Dashboard");
        }

        $this->view->message = $message;
    }

    public function tagsAction()
    {
        $newsManager = Core_Model_DiFactory::getNewsManager();
        $messageId   = $this->getParam("newsId");
        $form        = new Core_Form_News;

        if ($this->getRequest()->isPost() && ($newsManager->getMessage($messageId))){
            $form->setTagFields($this->getRequest()->getPost());

            if ($form->isValidPartial($this->getRequest()->getPost())){
                $this->view->result = $newsManager->addTags($messageId, $form->getValues());
            }
        }
    }

    public function emailSendMessage($messageId)
    {
        $newsManager = Core_Model_DiFactory::getNewsManager();
        
        if (!($message = $newsManager->getMessage($messageId))){
            return false;
        }

        $emailManager = Core_Model_DiFactory::getEmailManager();
        $emailManager->setOptions(array(
            "subject" => "[Maze.dashboard] - {$message["title"]}",
            "content" => $message["content"]
        ))->setToClients();

        return $emailManager->send();
    }
}
