<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../fairgate4/app/autoload.php';
include_once __DIR__.'/../fairgate4/var/bootstrap.php.cache';
include_once __DIR__.'/../fairgate4/app/config/fairgate_domains.php';

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.

$apcLoader = new Symfony\Component\ClassLoader\ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);

if (in_array($_SERVER['HTTP_HOST'], $domainArray)) {
    $kernel = new AppKernel('dev', true);
} else {
    $kernel = new AppKernel('domain', false);
}

$kernel->loadClassCache();
$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

