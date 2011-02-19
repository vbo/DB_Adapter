<?php

require_once dirname(__FILE__) . '/../Abstract/DBTest.php';

class DB_Adapter_Postgresql_DBTest extends DB_Adapter_Abstract_DBTest
{
    protected $_dbtype = 'postgresql';

    protected function _createTestTables()
    {
        @$this->_getDB()->query('
            CREATE TABLE ?_user (
                id int NOT NULL,
                login varchar(100) NOT NULL,
                mail varchar(400) NOT NULL,
                age int NOT NULL,
                active int DEFAULT 0 NOT NULL
            )'
        );
    }

    public function testInsertIntoTableWithRuleReturnsRuleBasedResult()
    {
        @$this->_getDB()->query("CREATE TABLE ?_ruletest(id SERIAL, str VARCHAR(10)) WITH OIDS");
        @$this->_getDB()->query("CREATE RULE ?_ruletest_r AS ON INSERT TO ?_ruletest DO (SELECT 111 AS id)");

        $this->assertEquals(
            $this->_getDB()->query('INSERT INTO ?_ruletest(str) VALUES (\'test\')'),
            array(
                array('id' => 111)
            )
        );
        
        @$this->_getDB()->query("DROP RULE ?_ruletest_r");
        @$this->_getDB()->query("DROP TABLE ?_ruletest");        
    }

    protected function _dropTestTables()
    {
        @$this->_getDB()->query('DROP TABLE ?_user');
    }
}
