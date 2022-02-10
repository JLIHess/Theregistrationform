<?php
/**
 * Base Class Core_View
 */
class Core_View
{
    public $layout;
    protected $_registry;
    public $controllerName;

    public $footerJs = '';
    public $headerJs = '';
    public $footerCss = '';
    public $headerCss = '';

    public function __construct($registry)
    {
        $this->_registry = $registry;
    }

    public function __get($key)
    {
        return $this->_registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->_registry->set($key, $value);
    }

    public function render($template, $data = null)
    {
        if(is_array($data)) {
            extract($data);
        }
        $rout = explode('/', $template);
        if (count($rout) == 1) {
            $template = $this->controllerName  . DS . $template;
        }
        if (count(explode('.', $template)) == 1) {
            $template .= '.php';
        }
        ob_start();
        require(APPLICATION_PATH . DS . 'views' . DS . 'scripts' . DS . $template);
        $content = ob_get_contents();
        ob_end_clean();

        if (count(explode('.', $this->layout)) == 1) {
            $this->layout .= '.php';
        }
        include(APPLICATION_PATH . DS . 'views' . DS . 'layout' . DS . $this->layout);
        die();
    }

    public function renderPartial($template, $data = null, $output = true)
    {
        if(is_array($data)) {
            extract($data);
        }
        $rout = explode('/', $template);
        if (count($rout) == 1) {
            $template = $this->controllerName  . DS . $template;
        }
        if (count(explode('.', $template)) == 1) {
            $template .= '.php';
        }

        ob_start();

        require(APPLICATION_PATH . DS . 'views' . DS . 'scripts' . DS . $template);
        $content = ob_get_contents();
        ob_end_clean();

        if ($output) {
            echo $content;
        } else {
            return $content;
        }
    }

    public function renderFile($filePatch, $data = null, $output = false)
    {

        if(is_array($data)) {
            extract($data);
        }

        $content = file_get_contents($filePatch);

        if ($output) {
            echo $content;
        } else {
            return $content;
        }
    }

    public function renderJsScripts($paths = array(), $position)
    {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                $this->{$position . 'Js'} .= '<script type="text/javascript" src="' . $path . '"></script>';
            }
        } else {
            $this->{$position . 'Js'} = '<script type="text/javascript" src="' . $paths . '"></script>';
        }
    }

    public function renderCssScripts($paths = array(), $position)
    {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                $this->{$position . 'Css'} .= '<link rel="stylesheet" href="' . $path . '" />';
            }
        } else {
            $this->{$position . 'Css'} = '<link rel="stylesheet" href="' . $paths . '" />';
        }
    }

    public function redirect($url)
    {
        ob_start();
        header('Location: ' . BASE_URL . '/' . $url);
        ob_end_clean();
        die();
    }
}
