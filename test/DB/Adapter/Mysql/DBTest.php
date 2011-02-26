<?php

require_once dirname(__FILE__) . '/../Abstract/DBTest.php';

class DB_Adapter_Mysql_DBTest extends DB_Adapter_Abstract_DBTest
{
    protected $_dbtype = 'mysql';

    /**
     * Performs direct DB query
     */
    private function _query($query)
    {
        return @mysql_query($query, $this->_getDB()->getLink());
    }

    private function _fetchCell($query)
    {
        return mysql_result($this->_query($query), 0);
    }

    public function testQueryPerforms()
    {
        $name = md5(time()) . 'db_adapter_unittest';
        $setted = 'bla';
        $this->_getDB()->query("SET @session.{$name} = '{$setted}'");
        $getted = $this->_fetchCell('SELECT @session.' . $name);
        $this->assertEquals($getted, $setted);
    }

    public function testSelectRow()
    {
        $user = $this->_getDB()->fetchRow('SELECT * FROM test_user WHERE id = 1');
        $this->assertEquals($user, array(
            'id' => 1,
            'login' => 'testTest',
            'age' => 21,
            'active' => 1
        ));
    }

    public function testSelectCell()
    {
        $login = $this->_getDB()->fetchCell('SELECT login FROM test_user WHERE id = 1');
        $this->assertEquals($login, 'testTest');
    }

    protected function _createTestTables()
    {
        $this->_query('
            CREATE TABLE test_user (
                id int NOT NULL,
                login varchar(100) NOT NULL,
                age int NOT NULL,
                active int DEFAULT 0 NOT NULL
            )'
        );
        $this->_query("INSERT INTO test_user VALUES (1, 'testTest',  21, 1)");
        $this->_query("INSERT INTO test_user VALUES (2, 'testTest2', 28, 0)");
    }

    protected function _dropTestTables()
    {
        $this->_query('DROP TABLE test_user');
    }
}
