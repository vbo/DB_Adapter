<?php

require_once dirname(__FILE__) . '/../../../lib/config.php';
require_once 'DB/Adapter/Factory.php';

abstract class DB_Adapter_AbstractTest extends PHPUnit_Framework_TestCase
{
    private static $_dsn;
    private static $_connections = array();
    protected $_dbtype;

    /**
     * @return DB_Adapter_Generic_DB
     */
    protected function _getDB()
    {
        if (!$this->_dbtype) {
            throw new Exception('$_dbtype must be setted in derived class');
        }
        if (empty(self::$_dsn)) {
            self::$_dsn = parse_ini_file(dirname(__FILE__) . '/../../../config/db-credentials.ini');
        }
        if (empty(self::$_connections[$this->_dbtype])) {
            $con = DB_Adapter_Factory::connect(self::$_dsn[$this->_dbtype]);
            $con->setIdentPrefix('test_');
            self::$_connections[$this->_dbtype] = $con;
        }
        return self::$_connections[$this->_dbtype];
    }
}
