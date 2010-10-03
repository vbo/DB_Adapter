<?php

require_once 'DB/Adapter/Generic/DB.php';

/**
 * PostreSQL DB implementation
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
 */
class DB_Adapter_Postgresql_DB extends DB_Adapter_Generic_DB
{
    private $link;
    private $config;
    private $prepareCache = array();

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->link = $this->_connect();
    }

    /**
     * @see http://php.net/manual/en/function.pg-connect.php
     */
    private function _connect()
    {
        $connectionParams = array();
        $recognizedParams = array('host', 'port', 'user');
        foreach ($recognizedParams as $param) {
            if (!empty($this->config[$param])) {
                $connectionParams[] = $param . '=' . $this->config[$param];
            }
        }

        if (!empty($this->config['pass'])) {
            $connectionParams[] = 'password=' . $this->config['pass'];
        }

        $dbname = preg_replace('{^/}s', '', $this->config['path']);
        if (!empty($dbname)) {
            $connectionParams[] = 'dbname=' . $dbname;
        }

        $connectionString = join(' ', $connectionParams);
        $link = @pg_connect($connectionString);

        if (!$link) {
            if (!error_reporting()) {
                return;
            }
            require_once 'DB/Adapter/Exception/ConnectionError.php';
            throw new DB_Adapter_Exception_ConnectionError(
                0, 'Connection failed', "pg_connect('{$connectionString}')", $this
            );
        }
        return $link;
    }

    /**
     * @return string
     */
    protected function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent) {
            return "E'" . pg_escape_string($this->link, $s) . "'";
        } else {
            return '"' . str_replace('"', '_', $s) . '"';
        }
    }

    /**
     * @return DB_Adapter_Generic_Blob $blob
     */
    protected function _performNewBlob($blobid=null)
    {
        require_once 'DB/Adapter/Postgresql/Blob.php';
        $obj = new DB_Adapter_Postgresql_Blob($this, $blobid);
        return $obj;
    }

    /**
     * @return array $fields List of BLOB fields names in result set
     */
    protected function _performGetBlobFieldNames($result)
    {
        $blobFields = array();
        for ($i=pg_num_fields($result)-1; $i>=0; $i--) {
            $type = pg_field_type($result, $i);
            if (strpos($type, "BLOB") !== false) {
                $blobFields[] = pg_field_name($result, $i);
            }
        }
        return $blobFields;
    }

    /**
     * Transform query different way specified by $how.
     * May return some information about performed transform.
     * @param array& $queryMain
     * @param string $how
     */
    protected function _performTransformQuery(array& $queryMain, $how)
    {
        switch ($how) {
            // Prepare total calculation (if possible)
            case 'CALC_TOTAL':
                // Not possible
                return true;

            // Perform total calculation.
            case 'GET_TOTAL':
                // TODO: GROUP BY ... -> COUNT(DISTINCT ...)
                $re = '/^
                    (?> -- [^\r\n]* | \s+)*
                    (\s* SELECT \s+)                                             #1
                    (.*?)                                                        #2
                    (\s+ FROM \s+ .*?)                                           #3
                        ((?:\s+ ORDER \s+ BY \s+ .*?)?)                          #4
                        ((?:\s+ LIMIT \s+ \S+ \s* (?: OFFSET \s* \S+ \s*)? )?)   #5
                $/six';
                $m = null;
                if (preg_match($re, $queryMain[0], $m)) {
                    $queryMain[0] = $m[1] . $this->_fieldList2Count($m[2]) . " AS C" . $m[3];
                    $skipTail = substr_count($m[4] . $m[5], '?');
                    if ($skipTail) {
                        array_splice($queryMain, -$skipTail);
                    }
                }
                return true;
        }

        return false;
    }

    /**
     * Must return:
     * - For SELECT queries: ID of result-set (PHP resource).
     * - For other  queries: query status (scalar).
     * - For error  queries: null.
     * @return mixed $result
     */
    protected function _performQuery(array $queryMain)
    {
        $this->_lastQuery = $queryMain;
        $isInsert = preg_match('/^\s* INSERT \s+/six', $queryMain[0]);

        //
        // Note that in case of INSERT query we CANNOT work with prepare...execute
        // cache, because RULEs do not work after pg_execute(). This is a very strange
        // bug... To reproduce:
        //   $DB->query("CREATE TABLE test(id SERIAL, str VARCHAR(10)) WITH OIDS");
        //   $DB->query("CREATE RULE test_r AS ON INSERT TO test DO (SELECT 111 AS id)");
        //   print_r($DB->query("INSERT INTO test(str) VALUES ('test')"));
        // In case INSERT + pg_execute() it returns new row OID (numeric) instead
        // of result of RULE query. Strange, very strange...
        //

        if (!$isInsert) {
            $this->_expandPlaceholders($queryMain, true);
            $hash = md5($queryMain[0]);
            if (!isset($this->prepareCache[$hash])) {
                $this->prepareCache[$hash] = true;
                $prepared = @pg_prepare($this->link, $hash, $queryMain[0]);                
                if ($prepared === false) {                
                    return $this->_raiseError($queryMain[0], pg_last_error($this->link));
                }
            } else {
                // Prepare cache hit!
            }            
            $result = @pg_execute($this->link, $hash, array_slice($queryMain, 1));
        } else {            
            $this->_expandPlaceholders($queryMain, false);
            $result = @pg_query($this->link, $queryMain[0]);
        }

        if ($result === false) {
            return $this->_raiseError($queryMain[0], pg_last_error($this->link));
        }
        if (!pg_num_fields($result)) {
            if ($isInsert) {
                // INSERT queries return generated OID (if table is WITH OIDs).
                //
                // Please note that unfortunately we cannot use lastval() PostgreSQL
                // stored function because it generates fatal error if INSERT query
                // does not contain sequence-based field at all. This error terminates
                // the current transaction, and we cannot continue to work nor know
                // if table contains sequence-updateable field or not.
                //
                // To use auto-increment functionality you must invoke
                //   $insertedId = $DB->query("SELECT lastval()")
                // manually where it is really needed.
                //
                return @pg_last_oid($result);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return @pg_affected_rows($result);
        }
        return $result;
    }

    /**
     * Fetch ONE NEXT row from result-set.
     * Must return:
     * - For SELECT queries: all the rows of the query (2d array).
     * - For INSERT queries: ID of inserted row.
     * - For UPDATE queries: number of updated rows.
     * - For other  queries: query status (scalar).
     * - For error  queries: throw an Exception.
     * @return mixed $result
     */
    protected function _performFetch($result)
    {
        $row = @pg_fetch_assoc($result);
        if (pg_last_error($this->link)) {
            return $this->_raiseError($this->_lastQuery);
        }
        if ($row === false) {
            return null;
        }
        return $row;
    }

    /**
     * Start new transaction.
     * @return mixed $result
     */
    protected function _performTransaction($mode=null)
    {
        return $this->query('BEGIN');
    }

    /**
     * Commit the transaction.
     * @return mixed $result
     */
    protected function _performCommit()
    {
        return $this->query('COMMIT');
    }

    /**
     * Rollback the transaction.
     * @return mixed $result
     */
    protected function _performRollback()
    {
        return $this->query('ROLLBACK');
    }

    /**
     * @todo Fix it
     */
    protected function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            /\* .*?                          \*/      # comments
        ';
    }

    protected function _performGetNativePlaceholderMarker($n)
    {
        return '$' . ($n + 1);
    }

    private function _raiseError($query, $error=null)
    {
        $this->rollback();
        $this->_lastQuery = $query;
        if (!error_reporting()) {
            return;
        }
        require_once 'DB/Adapter/Exception/QueryError.php';
        throw new DB_Adapter_Exception_QueryError(
            null, $this->getLastQuery(), empty($error) ? pg_last_error($this->link) : $error, $this
        );
    }
}