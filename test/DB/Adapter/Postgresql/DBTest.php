<?php

require_once dirname(__FILE__) . '/../Abstract/DBTest.php';

class DB_Adapter_Postgresql_DBTest extends DB_Adapter_Abstract_DBTest
{
    protected $_dbtype = 'postgresql';
}
