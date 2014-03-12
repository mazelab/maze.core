<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

$runtimeContext = isset($argv[1])? $argv[1] : 'production';

/** Zend_Application */
require_once APPLICATION_PATH . '/../vendor/autoload.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    $runtimeContext,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

$logger = Core_Model_DiFactory::getLogger();

if(Core_Model_DiFactory::getModuleSync()->sync()) {
    echo "plugin update successful";
    $logger->setMessage('maze module update successful!');
} else {
    echo "plugin update failed";
    $logger->setMessage('maze  module update failed!');
}

$logger->setType(Core_Model_Logger::TYPE_NOTIFICATION)->save();