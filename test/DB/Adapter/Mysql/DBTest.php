<?php

require_once dirname(__FILE__) . '/../Abstract/DBTest.php';

class DB_Adapter_Mysql_DBTest extends DB_Adapter_Abstract_DBTest
{
    protected $_dbtype = 'mysql';

    protected function _createTestTables()
    {
        $this->_getDB()->query('
            CREATE TABLE ?_user (
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
        $this->_getDB()->query('DROP TABLE ?_user');
    }
}
