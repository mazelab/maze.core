<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiLogController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiLogsController extends MazeLib_Rest_Controller
{
    /**
     * get paginated domains
     */
    public function getResourcesAction()
    {
        $logManager = Core_Model_DiFactory::getLogManager();
        $logAction = $this->getRequest()->getQuery("action");
        $logTypes = $this->getParam("type");
        $logLimit = $this->getParam("limit", 100);

        if ($this->getParam("client")) {
            $logs = $logManager->getClientLogs($this->getParam("Client"), $logLimit);
        } else if ($this->getParam("domain")) {
            $logs = $logManager->getDomainLogs($this->getParam("domain"), $logLimit);
        } else if ($this->getParam("module")) {
            $logs = $logManager->getModuleLogs($this->getParam("module"), $logLimit);
        } else if ($this->getParam("node")) {
            $logs = $logManager->getNodeLogs($this->getParam("node"), $logLimit);
        } else if ($this->getParam("context")) {
            $logs = array($logManager->getContextLog($this->getParam("context"), $logTypes, $logAction));
        } else if ($logTypes == "warnings") {
            $logs = $logManager->getWarnings($logLimit);
        } else if ($logTypes == "errors") {
            $logs = $logManager->getErrors($logLimit);
        } else if ($logTypes == "conflicts") {
            $logs = $logManager->getConflicts($logLimit);
        } else if ($logTypes == "successes") {
            $logs = $logManager->getSuccesses($logLimit);
        } else {
            $logs = $logManager->getLogs($logLimit);
        }

        $jsonLogs = array();
        foreach($logs as $log) {
            array_push($jsonLogs, $logManager->translateLog($log));
        }

        if (empty($jsonLogs)) {
            $this->_setNoContentHeader();
        }

        $this->_helper->json->sendJson($jsonLogs);
    }
}
