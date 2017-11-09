<?php
error_reporting(E_ALL);

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('TEST_DIR')) {
    define('TEST_DIR', dirname(__FILE__));
}
if (!defined('LIB_DIR')) {
    define('LIB_DIR', dirname(TEST_DIR) . DS . 'src');
}
if (!defined('RESOURCES_DIR')) {
    define('RESOURCES_DIR', TEST_DIR . DS . 'resources');
}

// Since the base test is not named "BaseTest", we need to load it manually.
require_once TEST_DIR . DS . 'BaseTestCase.php';
