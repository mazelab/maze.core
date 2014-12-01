<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ErrorController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->_layout->setLayout('layout');

        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('error', 'json')
            ->initContext();
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        if (($log = $this->getLogs())) {
            $log->err($errors->exception);
            $log->err($errors->request->getParams());
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exceptionMessage = $errors->exception->getMessage();
            $this->view->exceptionTrace = $errors->exception->getTraceAsString();
        }

        $this->view->requestParams = var_export($errors->request->getParams(), true);
    }

    public function getLogs()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

}

