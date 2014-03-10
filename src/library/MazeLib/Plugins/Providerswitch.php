<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Plugins_Providerswitch
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Plugins_Providerswitch extends Zend_Controller_Plugin_Abstract
{

    /**
     * @param Zend_Controller_Request_Abstract $request 
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_checkAdapterSwitch($request);
    }

    /**
     * @param Zend_Controller_Request_Abstract $request 
     * @return boolean 
     */
    protected function _checkAdapterSwitch(Zend_Controller_Request_Abstract $request)
    {
        $curProvider = $request->getCookie('_switchProvider', null)?
                ucfirst($request->getCookie('_switchProvider')) :
                ucfirst(Core_Model_Dataprovider_DiFactory::DEFAULT_ADAPTER);
        $newProvider = ucfirst($request->getParam('_switchProvider', null));
        
        if($request->getCookie('_switchProvider', null) && !$newProvider) {
            if($this->_switchAdapter($curProvider) !== $curProvider)
                return false;
        }
        if ($newProvider) {
            if($newProvider !== $curProvider) {
                if (Core_Model_Dataprovider_DiFactory::DEFAULT_ADAPTER == $newProvider) {
                    setcookie('_switchProvider', "", time() - 28800); // delete
                } else {
                    setcookie('_switchProvider', $newProvider, time() + 28800);
                }

                if($this->_switchAdapter($newProvider) === $newProvider) {
                    Zend_Auth::getInstance()->clearIdentity();
                    
                    $redirect = new Zend_Controller_Action_Helper_Redirector();
                    $redirect->gotoRoute(array(), 'login');
                }
                
                return false;
            } else {
                if (Core_Model_Dataprovider_DiFactory::DEFAULT_ADAPTER !== $curProvider)
                    setcookie('_switchProvider', $curProvider, time() + 28800);
                if($this->_switchAdapter($curProvider) !== $curProvider)
                    return false;
            }
        }
        
        return true;
    }

    /**
     * @param string $provider
     * @return string 
     */
    protected function _switchAdapter($provider)
    {
        $provider = ucfirst(strtolower($provider));
        if($provider !== Core_Model_Dataprovider_DiFactory::getAdapter()) {
            Core_Model_Dataprovider_DiFactory::setAdapter($provider);
        }
        
        return Core_Model_Dataprovider_DiFactory::getAdapter();
    }

}

