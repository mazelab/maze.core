<?php
/**
 * maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Controller
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Rest_Controller extends Zend_Controller_Action
{

    /**
     * These methods will return http error code 405 when not implemented. Everything else will throw 501.
     *
     * @var array
     */
    protected $_knownMethods = array(
        'PUT', 'POST', 'DELETE', 'HEAD', 'GET'
    );

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
     * sets not implemented header and clears body
     */
    protected function _setNotImplementedHeader() {
        $this->getResponse()->setHttpResponseCode(501);
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
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $this->getResponse()->setHeader('Content-Type', 'application/json');

        $requestMethod = strtolower($this->getRequest()->getServer("REQUEST_METHOD"));
        $requestAction = ucfirst($this->getParam("action"));

        if (method_exists($this, "{$requestMethod}{$requestAction}Action")) {
            $action = "{$requestMethod}-{$this->getParam("action")}";
        } elseif(in_array(strtoupper($requestMethod), $this->_knownMethods)) {
            $action = "method-not-allowed";
        } else {
            $action = "not-implemented";
        }

        $this->getRequest()->setActionName($action);
    }

    /**
     * sets method not allowed header
     */
    public function methodNotAllowedAction()
    {
        $this->_setMethodNotAllowedHeader();
    }

    /**
     * sets not implemented header
     */
    public function notImplementedAction()
    {
        $this->_setNotImplementedHeader();
    }

}
