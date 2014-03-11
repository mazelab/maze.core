<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(dirname(__DIR__)) . '/src/application'));

// Define path to application test directory
defined('APPLICATION_TEST_PATH')
    || define('APPLICATION_TEST_PATH', realpath(dirname(__FILE__)));

/** Zend_Application */
require_once APPLICATION_TEST_PATH . '/../../src/vendor/autoload.php';

set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_TEST_PATH . '/library',
    get_include_path(),
)));

// Create application, bootstrap, and run
$application = new Zend_Application(
    'testing',
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Core_');
