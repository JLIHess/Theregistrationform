<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(__FILE__));

require APPLICATION_PATH . '/../vendor/autoload.php';

// setting include paths. All resources would be taken from there
set_include_path(APPLICATION_PATH . DS . 'libs' . DS . PATH_SEPARATOR . APPLICATION_PATH . DS . 'models' . DS);

spl_autoload_register(function ($class) {
    $paths = explode(PATH_SEPARATOR, get_include_path());
    foreach ($paths AS $path) {
        $file = $path . '/' . str_replace('_', DS, $class) . '.php';
        if (is_file($file)) {
              include_once($file);
              return;
        }
    }
});

// Magic Quotes Fix
if (ini_get('magic_quotes_gpc')) {
    function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[clean($key)] = clean($value);
            }
        } else {
            $data = stripslashes($data);
        }

        return $data;
    }
    $_GET = clean($_GET);
    $_POST = clean($_POST);
    $_REQUEST = clean($_REQUEST);
    $_COOKIE = clean($_COOKIE);
}
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

$registry = new Core_Registry();

include_once(APPLICATION_PATH . DS . 'config' . DS . 'application.php');

$db = new Core_Database(DATABASE, HOST, USERNAME, PASSWORD);
$registry->set('db', $db);

$registry->set('model', Core_Model::init($registry));

$auth = new Core_Auth();
$registry->set('user', $auth);

$request = new Core_Request();
$registry->set('request', $request);

$controller = new Core_Controller($registry);
$registry->set('controller', $controller);

$route = new Core_Route($registry);
$registry->set('route', $route);
