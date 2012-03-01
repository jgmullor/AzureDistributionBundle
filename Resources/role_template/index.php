<?php

// Try to find the application root directory.
$appRoot = "E:\approot";
if (isset($_SERVER['ApplicationPath'])) {
    $appRoot = $_SERVER['ApplicationPath'] . '\app';
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

