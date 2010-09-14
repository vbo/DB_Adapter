<?php

require_once 'DB/Adapter/AbstractTest.php';

/**
 * @group Mysql
 * @group All
 */
class DB_Adapter_MysqlTest extends DB_Adapter_AbstractTest
{
    protected $_dbtype = 'mysql';
}