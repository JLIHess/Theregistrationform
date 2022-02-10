<?php

/**
 * Class Core_Model
 */
class Core_Model
{
    /**
     * @var $db Core_Database
     */
    public static $db;
    /**
     * @var $_registry Core_Registry
     */
    public static $_registry;

    public static function init($registry)
    {
        self::$_registry = $registry;
        self::$db = self::$_registry->get('db');
    }

    public function getVar($sql, $params = array())
    {
        self::$db->query($sql, $params);
        return self::$db->fetchColumn();
    }

    public function getRow($sql, $params = array())
    {
        self::$db->query($sql, $params);
        return self::$db->fetchRow();
    }

    public function getResults($sql, $params = array())
    {
        self::$db->query($sql, $params);
        return self::$db->fetchAll();
    }

    public function query($sql)
    {
        self::$db->query($sql);
    }
}
