<?php

require_once 'DB/Adapter/Generic/DB.php';

/**
 * MySQL DB implementation
 *
 * @package DB_Adapter
 *
 * DB_Adapter PHP library provides elegant interface for some SQL databases.
 * It supports several types of handy and secure placeholders
 * and provide comfortable debugging.
 *
 * (c) DB_Adapter community
 * @see http://db-adapter.in-source.ru
 * 
 * Original idea by Dmitry Koterov and Konstantin Zhinko
 * @see http://dklab.ru/lib/DbSimple/
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * @see http://www.gnu.org/copyleft/lesser.html
 *
 * @author  Borodin Vadim <vb@in-source.ru>
 * @version 10.10 beta
 *
 * @todo Add more comments
 */
class DB_Adapter_MySQL_DB extends DB_Adapter_Generic_DB
{
    private $_link;
    private $_config;

    /**
     * Class constructor.
     * Connect to MySQL.
     * @param array $config Parsed DSN
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
        $this->_link = $this->_connect();
        $this->_selectDB();
        if (isset($config["charset"])) {
            $this->query('SET NAMES ?', $config["charset"]);
        }
    }

    private function _connect()
    {
        $c = $this->_config;
        if (!empty($c['port'])) {
            $c['host'] .= ":{$c['port']}";
        }
        
        $con = @mysql_connect($c['host'], $c['user'], $c['pass'], true);
        if (!$con) {
            $this->_raiseConnectionError(
                'mysql_connect', array($c['host'], $c['user'], $c['pass'])
            );
        }

        return $con;
    }

    private function _selectDB()
    {
        $dbname = preg_replace('{^/}s', '', $this->_config['path']);
        $db_selected = @mysql_select_db($dbname, $this->_link);
        if (!$db_selected) {
            return $this->_raiseConnectionError('mysql_select_db', array($dbname));
        }
    }

    protected function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent) {
            $s = mysql_real_escape_string($s, $this->_link);
            return "'{$s}'";
        }

        $s = str_replace('`', '``', $s);
        return "`{$s}`";
    }

    protected function _performTransaction($parameters=null)
    {
        return $this->query('BEGIN');
    }

    protected function _performNewBlob($blobid=null)
    {
        require_once 'DB/Adapter/Mysql/Blob.php';
        return new DB_Adapter_Mysql_Blob($this, $blobid);
    }

    protected function _performGetBlobFieldNames($result)
    {
        $blob_fields = array();
        for ($i = mysql_num_fields($result) - 1; $i >= 0; $i--) {
            $its_blob = strpos(mysql_field_type($result, $i), "BLOB") !== false;
            if ($its_blob) {
                $blob_fields[] = mysql_field_name($result, $i);
            }
        }

        return $blob_fields;
    }

    protected function _performCommit()
    {
        return $this->query('COMMIT');
    }

    protected function _performRollback()
    {
        return $this->query('ROLLBACK');
    }

    protected function _performTransformQuery(array& $queryMain, $how)
    {
        switch ($how) {
            // Prepare total calculation (if possible)
            case 'CALC_TOTAL':
                $m = null;
                if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m)) {
                    $queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
                }
                break;

            // Perform total calculation.
            case 'GET_TOTAL':
                $queryMain = array('SELECT FOUND_ROWS()');
                break;

            default:
                return false;
                break;
        }
        return true;
    }

    protected function _performQuery(array $queryMain)
    {
        $this->_lastQuery = $queryMain;
        $this->_expandPlaceholders($queryMain, false);
        $result = @mysql_query($queryMain[0], $this->_link);

        if ($result === false) {
            return $this->_raiseQueryError();
        }
        if (!is_resource($result)) {
            // INSERT queries return generated ID.
            if (preg_match('/^\s* INSERT \s+/six', $queryMain[0])) {
                return @mysql_insert_id($this->_link);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return @mysql_affected_rows($this->_link);
        }
        return $result;
    }

    protected function _performFetch($result)
    {
        $row = @mysql_fetch_assoc($result);
        if (mysql_error()) {
            return $this->_raiseQueryError();
        }
        if ($row === false) {
            return null;
        }
        return $row;
    }

    protected function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            `   (?> [^`]+ | ``)*              `   |   # backticks
            /\* .*?                          \*/      # comments
        ';
    }

    private function _raiseQueryError()
    {
        if (!error_reporting()) {
            return;
        }
        require_once 'DB/Adapter/Exception/QueryError.php';
        throw new DB_Adapter_Exception_QueryError(
            mysql_errno($this->_link), $this->getLastQuery(), mysql_error($this->_link), $this
        );
    }

    private function _raiseConnectionError($func, $conn_params)
    {
        if (!error_reporting()) {
            return;
        }
        $errno = $this->_link ? mysql_errno($this->_link) : mysql_errno();
        $error = $this->_link ? mysql_error($this->_link) : mysql_error();
        $str_params = join("', '", $conn_params);
        $primary_info = "{$func} ('{$str_params}')";
        require_once 'DB/Adapter/Exception/ConnectionError.php';
        throw new DB_Adapter_Exception_ConnectionError($errno, $primary_info, $error, $this);
    }

    protected function  _performGetNativePlaceholderMarker($n)
    {
        return '?';
    }
}