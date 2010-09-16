<?php

require_once 'DB/Adapter/AbstractTest.php';

/**
 * @group Mysql
 * @group All
 */
class DB_Adapter_MysqlTest extends DB_Adapter_AbstractTest
{
    protected $_dbtype = 'mysql';

    public function testStringPH() {}
    public function testDigitPH() {}
    public function testFloatPH() {}
    public function testLinkPH() {}
    public function testListPH() {}
    public function testHashPH() {}
    public function testIdPH() {}
}