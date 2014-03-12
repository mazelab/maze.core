<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ModulesController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ModulesController extends Zend_Controller_Action
{

    /**
     * message when module installation failed
     */
    CONST MESSAGE_MODULE_DEINSTALL_FAILED = 'Couldn\'t deinstall module %1$s';
    
    /**
     * message when module installation failed
     */
    CONST MESSAGE_MODULE_INSTALL_FAILED = 'Couldn\'t install module %1$s';
    
    /**
     * message when module wasn't found
     */
    CONST MESSAGE_MODULE_NOT_FOUND = 'Module %1$s not found';
    
    /**
     * message when module update failed
     */
    CONST MESSAGE_MODULE_UPDATE_FAILED = 'Couldn\'t update module %1$s';
    
    /**
     * message when module update succeeded
     */
    CONST MESSAGE_MODULE_UPDATE_SUCCESS = 'Module %1$s was updated';
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("addadditionalfield", "json")
                    ->addActionContext("updateadditionalfield", "json")
                    ->addActionContext("install", "json")
                    ->addActionContext("deinstall", "json")
                    ->addActionContext("update", "json")
                    ->addActionContext("sync", "json")
                    ->initContext();

        // set view messages from MessageManager
        $this->_helper->getHelper("SetDefaultViewVars");
        
        // sync module data on daily basis
        $moduleSync = Core_Model_DiFactory::getModuleSync();
        $moduleSync->syncDaily();
    }

    public function indexAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();

        $this->view->updateableModules = $moduleManager->getUpdateableModules();
        $this->view->availableModules = $moduleManager->getAvailableModules();
        $this->view->installedModules = $moduleManager->getInstalledModules();

        $this->view->lastModuleSync = Core_Model_DiFactory::getConfig()->getLastModuleSync(true);
    }
    
    public function installAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        $module = $moduleManager->getModule($this->getParam('moduleName'));
        
        if(!$module || !$moduleManager->installModule($module->getName())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_INSTALL_FAILED, $this->getParam('moduleName'));
        }else {
            $this->view->result = true;
            $this->view->route = $this->view->url(array($module->getName()), "moduleDetail");
        }
    }
    
    public function deinstallAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        $module = $moduleManager->getModule($this->getParam('moduleName'));
        
        if(!$module || !$moduleManager->deinstallModule($module->getName())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_DEINSTALL_FAILED, $this->getParam('moduleName'));
        }else {
            $this->view->result = true;
        }
    }
    
    public function detailAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        if((!$module = $moduleManager->getModule($this->getParam("moduleName")))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_NOT_FOUND, $this->getParam('moduleName'));
            return $this->forward('index');
        }
        
        $logManager = Core_Model_DiFactory::getLogManager();
        $form = new Core_Form_AdditionalInfo;
        $form->addAdditionalFields("additionalFields", (array) $module->getData("additionalFields"));
        
        $this->view->module = $module->getData();
        
        $this->view->form = $form;
        $this->view->config = $module->getModuleConfig();
        
        $this->view->logs = $logManager->getModuleLogs($module->getName());

        if(Core_Model_DiFactory::getModuleRegistry()->getModule($module->getName())) {
            $this->view->nodes = Core_Model_DiFactory::getModuleListings()
                    ->getNodesWithDomains($module->getName());
            $this->view->domains = Core_Model_DiFactory::getModuleListings()
                    ->getDomainsWithOwnerAndNodes($module->getName());
        }
        
        $navigation = $this->view->navigation();
        if (($active = $navigation->findActive($navigation->getContainer()))){
            $moduleName = $module->getData("label") ? $module->getData("label") : $module->getName();
            $active["page"]->setLabel($moduleName);
        }
    }
    
    public function updateAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        $module = $moduleManager->getModule($this->getParam('moduleName'));
        
        if(!$module || !$moduleManager->updateModule($module->getName())) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_UPDATE_FAILED, $this->getParam('moduleName'));
        }else {
            Core_Model_DiFactory::getMessageManager()
                    ->addSuccess(self::MESSAGE_MODULE_UPDATE_SUCCESS, $this->getParam('moduleName'));
            $this->view->result = true;
        }
    }

    public function addadditionalfieldAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        if((!$module = $moduleManager->getModule($this->getParam("moduleName")))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_NOT_FOUND, $this->getParam('moduleName'));
            return;
        }
        
        $form = new Core_Form_AdditionalInfo();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->_request->getPost())) {
                $this->view->result = $moduleManager->addAdditionalField($module->getName()
                        , $form->getValue("additionalKey") , $form->getValue("additionalValue"));
            } else {
                $this->view->formErrors = $form->getMessages(null, true);
            }
        }
    }

    public function syncAction()
    {
        $moduleSync = Core_Model_DiFactory::getModuleSync();

        $this->view->result = $moduleSync->sync();
    }

    public function updateadditionalfieldAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        if((!$module = $moduleManager->getModule($this->getParam("moduleName")))) {
            Core_Model_DiFactory::getMessageManager()
                    ->addError(self::MESSAGE_MODULE_NOT_FOUND, $this->getParam('moduleName'));
            return;
        }
        
        $result = false;
        if ($this->getRequest()->isPost("additionalFields")) {
            $result = $moduleManager->updateModuleAdditionalFields($module->getName(),
                    $this->getParam("additionalFields"));
        }

        $this->view->result = $result;
    }

}

