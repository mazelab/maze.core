<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

abstract class MazeLib_Rest_Controller extends Zend_Rest_Controller
{

    /**
     * set accepted header and clears body
     */
    protected function _setAcceptedHeader()
    {
        $this->getResponse()->setHttpResponseCode(202);
        $this->getResponse()->setBody(null);
    }

    /**
     * set method not allowed header and clears body
     */
    protected function _setMethodNotAllowedHeader()
    {
        $this->getResponse()->setHttpResponseCode(405);
        $this->getResponse()->setBody(null);
    }

    /**
     * set no content header and clears body
     */
    protected function _setNoContentHeader()
    {
        $this->getResponse()->setHttpResponseCode(204);
        $this->getResponse()->setBody(null);
    }

    /**
     * set not found header and clears body
     */
    protected function _setNotFoundHeader()
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->getResponse()->setBody(null);
    }

    /**
     * set server error header
     */
    protected function _setServerErrorHeader()
    {
        $this->getResponse()->setHttpResponseCode(500);
    }

    /**
     * initialize json context
     */
    public function init()
    {
//        if(($accept = $this->getRequest()->getHeader('Accept')) && strpos($accept, 'application/json') === false) {
//            throw new Zend_Controller_Action_Exception('Not Acceptable', 406);
//        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }

}
