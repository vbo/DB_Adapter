<?php

require_once dirname(__FILE__) . '/../Abstract/DBTest.php';

class DB_Adapter_Mysql_DBTest extends DB_Adapter_Abstract_DBTest
{
    protected $_dbtype = 'mysql';

    /**
     * Perform direct DB query
     */
    private function _query($query)
    {
        return @mysql_query($query, $this->_getDB()->getLink());
    }

    private function _selectCell($query)
    {
        return mysql_result($this->_query($query), 0);
    }

    public function testQueryPerforms()
    {
        $name = md5(time()) . 'db_adapter_unittest';
        $setted = 'bla';
        $this->_getDB()->query("SET @session.{$name} = '{$setted}'");
        $getted = $this->_selectCell('SELECT @session.' . $name);
        $this->assertEquals($getted, $setted);
    }

    protected function _createTestTables()
    {
        $this->_query('
            CREATE TABLE test_user (
                id int NOT NULL,
                login varchar(100) NOT NULL,
                mail varchar(400) NOT NULL,
                age int NOT NULL,
                active int DEFAULT 0 NOT NULL
            )'
        );
    }

    protected function _dropTestTables()
    {
        $this->_query('DROP TABLE test_user');
    }
}
