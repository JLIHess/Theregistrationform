<?php
/**
 * Main Config
 */

// Database connection settings
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    define('DATABASE', 'jli3-new');
    define('HOST', 'localhost');
    define('USERNAME', 'root');
    define('PASSWORD', '4449444');
} else {
    define('DATABASE', 'jli3-new');
    define('HOST', 'localhost');
    define('USERNAME', 'jli');
    define('PASSWORD', '077ilj');
}


define('DEFAULT_CONTROLLER', 'base');
define('DEFAULT_ACTION', 'index');