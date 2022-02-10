<?php
/**
 * Class Core_Database
 */
class Core_Database
{
    private $_dbh = null;
    private $_sth = null;

    public function __construct($database, $host, $username, $password) {
        try {
            $this->_dbh = new PDO('mysql:dbname=' . $database . ';host=' . $host, $username, $password);
        } catch (PDOException $e) {
            trigger_error('Connection failed: ' . $e->getMessage());
            die();
        }
    }

    public function getConnection()
    {
        return $this->_dbh;
    }

    public function query($sql, $params = array())
    {
        try {
            $this->_sth = $this->_dbh->prepare($sql);

            if (!empty($params) && is_array($params)) {
                foreach ($params as $key => $value) {
                    $this->_sth->bindParam($key, $value, PDO::PARAM_STR);
                }
            }

            $result = $this->_sth->execute();
        } catch (PDOException $e) {
            print $e->getMessage();
            die();
        }

        return $result;
    }

    public function fetchArray($sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }
    public function fetchAll($sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function fetchRow($sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->fetch();
        } else {
            return false;
        }
    }

    public function fetchColumn($colNumber = 0, $sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->fetchColumn($colNumber);
        } else {
            return false;
        }
    }

    function numRows($sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->rowCount();
        } else {
            return false;
        }
    }

    function insertId()
    {
        if (is_a($this->_dbh, 'PDO')) {
            return $this->_dbh->lastInsertId();
        } else {
            return null;
        }
    }

    function affectedRows($sth = null)
    {
        if (!is_a($sth, 'PDOStatement')) {
            $sth = $this->_sth;
        }
        if (is_a($sth, 'PDOStatement')) {
            return $sth->rowCount();
        } else {
            return false;
        }
    }
}
