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
 * @version 0.1 beta
 */
class DB_Adapter_Postgresql_DB extends DB_Adapter_Generic_DB
{
    private $link;
    private $config;

    public function  __construct(array $config)
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
        $recognizedParams = array('host', 'port', 'user', 'pass');
        foreach ($recognizedParams as $param) {
            if (!empty($this->config[$param])) {
                $connectionParams[] = $param . '=' . $this->config[$param];
            }
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
            throw new DB_Adapter_Exception_ConnectionError($errno, $primary_info, $error, $this);
        }

        return $link;
    }

    /**
     * @return string
     */
    protected function _performEscape($s, $isIdent=false)
    {

    }

    /**
     * @return DB_Adapter_Generic_Blob $blob
     */
    protected function _performNewBlob($blobid=null)
    {

    }

    /**
     * @return array $fields List of BLOB fields names in result set
     */
    protected function _performGetBlobFieldNames($result)
    {

    }

    /**
     * Transform query different way specified by $how.
     * May return some information about performed transform.
     * @param array& $queryMain
     * @param string $how
     */
    protected function _performTransformQuery(array& $queryMain, $how)
    {

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

    }

    /**
     * Start new transaction.
     * @return mixed $result
     */
    protected function _performTransaction($mode=null)
    {

    }

    /**
     * Commit the transaction.
     * @return mixed $result
     */
    protected function _performCommit()
    {
        
    }

    /**
     * Rollback the transaction.
     * @return mixed $result
     */
    protected function _performRollback()
    {
        
    }
}