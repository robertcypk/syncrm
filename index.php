<?php
header('Access-Control-Allow-Origin: *');

ini_set('max_execution_time', 600);
ini_set('soap.wsdl_cache_enabled',0);
ini_set('soap.wsdl_cache_ttl',0);

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

require __DIR__.'/config.php';
require __DIR__.'/app.php';
require __DIR__.'/app/http.php';