<?php

require_once 'DB/Adapter/AbstractTest.php';

/**
 * @group Postgresql
 * @group NotImplemented
 */
class DB_Adapter_PostgresqlTest extends DB_Adapter_AbstractTest
{
    protected $_dbtype = 'postgresql';

    public function stringPHDataProvider()
    {
        return array(
            array('1', "E'1'"),
            array(1, "E'1'"),
            array(0, "E'0'"),
            array('Hello world', "E'Hello world'"),
            array(null, "NULL"),
        );
    }

    public function listPHDataProvider()
    {
        return array(
            array(1, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
            array(null, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
        );
    }

    public function hashPHDataProvider()
    {
        return array(
            array(1, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
            array(null, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
        );
    }

    public function idPHDataProvider()
    {
        return array(
            array('some', "\"some\""),
            array(null, "\"\""),
        );
    }
}