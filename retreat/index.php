<?php
session_start();

// include config settings
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATCH', dirname(__FILE__));

define('BASE_URL', '/retreat_new/');

// Register scripts
require_once(BASE_PATCH . DS . 'application' . DS . 'bootstrap.php');

