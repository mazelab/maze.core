<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiModulesController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiModulesController extends MazeLib_Rest_Controller
{
    /**
     * get modules
     */
    public function getResourcesAction()
    {
        $filterModules = $this->getParam("filter");
        if ($filterModules == "available") {
            $modules = Core_Model_DiFactory::getModuleManager()->getAvailableModules();
        } else if ($filterModules == "installed") {
            $modules = Core_Model_DiFactory::getModuleManager()->getInstalledModules();
        } else if ($filterModules == "updateable") {
            $modules = Core_Model_DiFactory::getModuleManager()->getUpdateableModules();
        } else {
            $modules = array();
            foreach(Core_Model_DiFactory::getModuleRegistry()->getModules() as $module) {
                array_push($modules, $module->getData());
            }
        }

        if (empty($modules)) {
            return $this->_setNoContentHeader();
        }

        $jsonModules = array();
        foreach($modules as $module) {
            array_push($jsonModules, $module);
        }

        $this->_helper->json->sendJson($jsonModules);
    }

    public function postResourcesAction()
    {
        $this->_setNotImplementedHeader();
    }

    public function headResourcesAction()
    {
        $this->_setNotImplementedHeader();
    }

    public function getResourceAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleRegistry();
        if(!($module = $moduleManager->getModule($this->getParam("moduleName")))) {
            return $this->_setNotFoundHeader();
        }

        if($this->getParam('clientConfig')) {
            $result = $module->getClientConfig($this->getParam('clientConfig'));
        } elseif($this->getParam('nodeConfig')) {
            $result = $module->getClientConfig($this->getParam('nodeConfig'));
        } elseif($this->getParam('domainConfig')) {
            $result = $module->getClientConfig($this->getParam('domainConfig'));
        } else {
            $result = $module->getData();
        }

        $this->_helper->json->sendJson($result);
    }

    public function postResourceAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    public function headResourceAction()
    {
        $this->_setNotImplementedHeader();
    }

    public function putResourceAction()
    {
        $moduleManager = Core_Model_DiFactory::getModuleManager();
        if((!$module = $moduleManager->getModule($this->getParam("moduleName")))) {
            return $this->_setNotFoundHeader();
        }

        $form = new Core_Form_AdditionalInfo;
        $form->addAdditionalFields("additionalFields", (array) $module->getData("additionalFields"));
        $values = $form->getValidValues($this->getRequest()->getParams());

        if (isset($values["additionalValue"]) && isset($values["additionalKey"]) &&
            $moduleManager->addAdditionalField($module->getName(), $values)) {
            $this->getResponse()->setHttpResponseCode(202);
            $this->_helper->json->sendJson($module->getData());
        } else if (isset($values["additionalFields"]) &&
            $moduleManager->updateModuleAdditionalFields($module->getName(),
                $values["additionalFields"])){
            $this->getResponse()->setHttpResponseCode(202);
            $this->_helper->json->sendJson($module->getData());
        }
    }

    public function deleteResourceAction()
    {
        $this->_setNotImplementedHeader();
    }

}
