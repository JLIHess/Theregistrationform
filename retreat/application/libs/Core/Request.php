<?php

/**
 * Class Core_Request
 */
class Core_Request
{
    public function errorPage()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'] . BASE_URL . '404.html';
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
        header('Location:' . $host);
        die();
    }
    /*
    public function loginPage()
    {
        $host = 'http://' . $_SERVER['HTTP_HOST'] . BASE_APP_URL . '/login.php';
        header('Location:' . $host);
        die();
    }
    */

    public function redirect($url, $replace = true, $code = 301)
    {
        header('Location: ' . $url, $replace, $code);
        exit;
    }

    public function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            return true;
        } else {
            return false;
        }
    }
}
