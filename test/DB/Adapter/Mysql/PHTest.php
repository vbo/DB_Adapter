<?php

require_once dirname(__FILE__) . '/../Abstract/PHTest.php';

class DB_Adapter_Mysql_PHTest extends DB_Adapter_Abstract_PHTest
{
    protected $_dbtype = 'mysql';

    public function digitPHDataProvider()
    {
        return array(
            array(1, "1"),
            array('1a', "1"), // Php behavior
            array(1, "1"),
            array(null, "NULL"),
        );
    }

    public function floatPHDataProvider()
    {
        return array(
            array(1, "1"),
            array(1.5, "1.5"),
            array(null, "NULL"),
        );
    }

    public function stringPHDataProvider()
    {
        return array(
            array('1', "'1'"),
            array(1, "'1'"),
            array(0, "'0'"),
            array('Hello world', "'Hello world'"),
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
            array('some', "`some`"),
            array(null, "``"),
        );
    }

    public function linkPHDataProvider()
    {
        return array(
            array(1, "1"),
            array(null, "NULL"),
        );
    }
}
