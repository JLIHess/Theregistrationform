<?php

/**
 * Class Core_Route
 */
class Core_Route
{
    protected $_registry;

    public function __construct($registry)
    {
        $this->_registry = $registry;
        $this->_run();
    }

    public function __get($key)
    {
        return $this->_registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->_registry->set($key, $value);
    }

    protected function _run()
    {
        $controllerName = ucfirst($this->controller->controllerId);
        $actionName = ucfirst($this->controller->actionId);

        $routes = explode('/', preg_replace(array('/\?.*$/', '/index.php/'), '', $_SERVER['REQUEST_URI']));
        $queryStr = ($_SERVER['QUERY_STRING'] != '')? explode('&', $_SERVER['QUERY_STRING']) : array();

        $urlParams = array();
        foreach ($queryStr as $param) {
            $param = explode('=', $param);
            if (isset($param[1])) {
                $urlParams[$param[0]] = $param[1];
            }
        }

        $baseUrl = explode('/', BASE_URL);
        foreach ($baseUrl as $folder) {
            foreach ($routes as $key => $route) {
                if ($folder == $route) {
                    unset($routes[$key]);
                }
            }
        }

        foreach ($routes as $key => $route) {
            if (!empty($route) && !preg_match('/^[a-zA-Z]{1,}[0-9a-zA-Z]{1,}$/', $route)) {
                $routes = array();
                break;
            }
        }

        $routes = array_values($routes);

        if (!empty($routes[0])) {
            $controllerName = ucfirst($routes[0]);
            $this->controller->controllerId = $controllerName;
        }

        if (!empty($routes[1])) {
            $actionName = ucfirst($routes[1]);
            $this->controller->actionId = $actionName;
        }

        $controller = 'Controller_' . ucfirst(strtolower($controllerName));
        $controllerName = 'Controller_' .  $controllerName;
        $actionName = 'action_' . $actionName;

        $controllerPath = APPLICATION_PATH . DS . 'controllers' . DS . $controllerName . '.php';

        if (file_exists($controllerPath)) {
            include $controllerPath;
        } else {
            $this->request->errorPage();
        }

        $controller = new $controller($this->_registry);
        if (method_exists($controller, $actionName)) {
            $controller->$actionName($this->_registry);
        }
        else {
            $this->request->errorPage();
        }
    }
}