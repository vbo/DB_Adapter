<?php

require_once dirname(__FILE__) . '/../Abstract/PHTest.php';

class DB_Adapter_Postgresql_PHTest extends DB_Adapter_Abstract_PHTest
{
    protected $_dbtype = 'postgresql';

    public function digitPHDataProvider()
    {
        return array(
            array(10, '$1'),
        );
    }

    public function floatPHDataProvider()
    {
        return array(
            array(1.5, '$1'),
        );
    }

    public function stringPHDataProvider()
    {
        return array(
            array('test', '$1'),
        );
    }

    public function linkPHDataProvider()
    {
        return array(
            array(1, "1"),
            array(null, "NULL"),
        );
    }

    public function listPHDataProvider()
    {
        return array(
            array(array(1, '2'), "1, E'2'"),
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
