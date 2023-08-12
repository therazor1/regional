<?php

# CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

use Inc\Route;
use Libs\Pixie\QB;

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(0);
session_start();

header("Content-type: text/html; charset=UTF-8");

define('_PATH_', dirname(__FILE__)); // Directorio Base

require _PATH_ . '/vendor/autoload.php';
require _PATH_ . '/inc/helpers.php';

// QB::setupBase(stg('timezone'));

$route = new Route();
$route->add(':any', '%', '%');
// $route->dir('admin');
// $route->dir('app');
$route->dir('api');
// $route->dir('proveedor');
$route->dir('test');
$route->send();