<?php
/**
 * Base Class Core_Controller
 *
 * @property Core_Request $request
 */
class Core_Controller
{
    public $view;
    public $layout = 'main';
    public $controllerId = DEFAULT_CONTROLLER;
    public $actionId = DEFAULT_ACTION;
    protected $_registry;

    public function __construct($registry)
    {
        $this->_registry = $registry;

        $this->view = new Core_View($this->_registry);
        $this->view->layout = $this->layout;
        $this->view->controllerName = $this->controllerId;

        $this->init();
    }

    public function __get($key)
    {
        return $this->_registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->_registry->set($key, $value);
    }

    public function init()
    {
        return true;
    }
}