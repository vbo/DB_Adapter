<?php

require_once 'DB/Adapter/AbstractTest.php';

/**
 * @group Postgresql
 * @group NotImplemented
 */
class DB_Adapter_PostgresqlTest extends PHPUnit_Framework_TestCase
{
    protected $_dbtype = 'postgresql';


    public function setUp()
    {
        $this->_connect();
        $this->_createTestTables();
    }

    public function testConnectionSucceeded()
    {
        $this->assertNotNull($this->_DB);
    }

    protected function _connect()
    {
        if (empty(TestHelper::$dbInstances[$this->_dbtype])) {
            TestHelper::$dbInstances[$this->_dbtype] = DB_Adapter_Factory::connect(
                TestHelper::$dsn[$this->_dbtype]
            );
        }

        $this->_DB = TestHelper::$dbInstances[$this->_dbtype];
    }

    protected function _createTestTables()
    {
        @$this->_DB->query("DROP TABLE test_user");
        @$this->_DB->query("DROP TABLE test_tree");

        $this->_DB->query("
            CREATE TABLE test_user (
                id     int(11)      NOT NULL  auto_increment,
                login  varchar(100) NOT NULL,
                mail   varchar(400) NOT NULL,
                age    int(11)      NOT NULL,
                active boolean      DEFAULT FALSE NOT NULL,
                PRIMARY KEY (id)
            )
            ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
        );
    }

















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