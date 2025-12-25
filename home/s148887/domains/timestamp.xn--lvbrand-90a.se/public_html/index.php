<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

define('APP_PATH', dirname(__DIR__) . '/app');

require APP_PATH . '/core/Auth.php';
Auth::handle();

require APP_PATH . '/core/Router.php';
require APP_PATH . '/core/Controller.php';
require APP_PATH . '/core/Model.php';

$router = new Router();
$router->route();