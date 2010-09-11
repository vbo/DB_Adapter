<?php
require_once dirname(__FILE__) . '/../Generic/DBTest.php';

class DB_Adapter_Mysql_DBTest extends DB_Adapter_Generic_DBTest
{
    protected $_dbtype = 'mysql';
}