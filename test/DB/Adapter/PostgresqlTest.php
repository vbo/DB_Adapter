<?php

require_once 'DB/Adapter/AbstractTest.php';

/**
 * @group Postgresql
 * @group All
 */
class DB_Adapter_PostgresqlTest extends DB_Adapter_AbstractTest
{
    protected $_dbtype = 'postgresql';

    public function listPHDataProvider()
    {
        return array(
            array(array('a', 'b'), "E'a', E'b'"),
            array(1, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
            array(null, "DB_ADAPTER_ERROR_VALUE_NOT_ARRAY"),
        );
    }

    public function hashPHDataProvider()
    {
        return array(
            array(array('a'=>'b', 'c'=>'d'), '"a"=E\'b\', "c"=E\'d\''),
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