<?php
/**
 * Tests bootstrap
 *
 * @package Qis
 */
date_default_timezone_set('America/Chicago');

require_once 'BaseTestCase.php';

$root = realpath(dirname(dirname(__FILE__)));

require_once $root . '/vendor/autoload.php';

// Include path
$paths = array(
    '.',
    $root,
    $root . DIRECTORY_SEPARATOR . 'lib',
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $paths));

require_once 'Qis.php';
require_once 'QisModuleInterface.php';
require_once 'QisConfig.php';

if (!function_exists('get_called_class')) {
    include_once 'get_called_class.func.php';
}
