<?php

require_once dirname(__FILE__) . '/../AbstractTest.php';

abstract class DB_Adapter_Abstract_DBTest extends DB_Adapter_AbstractTest
{
    public function testQueryErrorExceptionRaises()
    {
        $this->setExpectedException('DB_Adapter_Exception_QueryError');
        $this->_getDB()->select('BAD QUERY');
    }

    public function testQueryErrorNotRaisesWithAtSimbol()
    {
        @$this->_getDB()->select('BAD QUERY');
    }

    public function testQueryErrorExceptionPrimaryInfo()
    {
        try {
            $this->_getDB()->select('BAD QUERY');
        } catch (DB_Adapter_Exception_QueryError $e) {
            $this->assertEquals('BAD QUERY', $e->primaryInfo);
        };
    }

    public function testGetLastQuery()
    {
        try {
            $a = $this->_getDB()->query('QUERY TEXT');
        } catch (DB_Adapter_Exception_QueryError $e) {
            $this->assertEquals('QUERY TEXT', $this->_getDB()->getLastQuery());
        }
    }

    public function testGetLastQueryInline()
    {
        try {
            $this->_getDB()->query("\t\t\t QUERY \n TEXT");
        } catch (DB_Adapter_Exception_QueryError $e) {
            $this->assertEquals('QUERY TEXT', $this->_getDB()->getLastQuery($inline = true));
        }
    }
}
