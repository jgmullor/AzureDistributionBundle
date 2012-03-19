<?php

// Try to find the application root directory.
// Env Variable 'ApplicationPath' is set by the add_environment_variables.ps1
// script. The others are "default" paths of Azure that can be tried as
// fallback.
if (isset($_SERVER['ApplicationPath'])) {
    $appRoot = $_SERVER['ApplicationPath'] . '\app';
} else if ( file_exists("E:\approot\app")) {
    $appRoot = "E:\approot\app";
} else if ( file_exists("F:\approot\app")) {
    $appRoot = "F:\approot\app";
} else {
    $appRoot = __DIR__ . '\..\..\approot\app';
}

require_once $appRoot . '\bootstrap.php.cache';
require_once $appRoot . '\AppKernel.php';
//require_once $appRoot . '\AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('azure', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

