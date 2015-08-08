<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Yaml\Parser;

// Set up project root path and require AppKernel to clear cache
define('PROJECT_ROOT_PATH', dirname(__DIR__));
require_once PROJECT_ROOT_PATH . '/app/bootstrap.php.cache';
require_once PROJECT_ROOT_PATH . '/app/AppKernel.php';
Debug::enable();

// Clear symfony cache
$kernel      = new AppKernel('test', true);
$application = new Application($kernel);
$application->setAutoExit(false);
$application->run(new ArgvInput(['app/console', 'cache:clear']));
$kernel->shutdown();

// Load global parameters
global $parameters;
$parameters = loadParameters();

/**
 * @return array
 */
function loadParameters()
{
    $parser       = new Parser;
    $configDir    = PROJECT_ROOT_PATH . '/app/config/';
    $testConfig   = $parser->parse(file_get_contents($configDir . 'parameters_test.yml'));
    $globalConfig = $parser->parse(file_get_contents($configDir . 'parameters.yml'));
    $parameters   = $testConfig['parameters'] + $globalConfig['parameters'];

    return $parameters;
}