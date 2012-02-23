<?php

if (isset($_SERVER['LOCALAPPDATA'])) {
    $appRoot = realpath($_SERVER['LOCALAPPDATA'] . '\..\..\app');
} else {
    $appRoot = "E:\approot";
}

require_once $appRoot . '\bootstrap.php.cache';
require_once $appRoot . '\AppKernel.php';
//require_once $appRoot . '\AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

