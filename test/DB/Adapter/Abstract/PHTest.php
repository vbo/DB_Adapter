<?php

require_once dirname(__FILE__) . '/../AbstractTest.php';

abstract class DB_Adapter_Abstract_PHTest extends DB_Adapter_AbstractTest
{
    /**
     * @dataProvider stringPHDataProvider
     */
    public function testStringPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider digitPHDataProvider
     */
    public function testDigitPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?d', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider floatPHDataProvider
     */
    public function testFloatPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?f', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider linkPHDataProvider
     */
    public function testLinkPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?n', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider listPHDataProvider
     */
    public function testListPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider hashPHDataProvider
     */
    public function testHashPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    /**
     * @dataProvider idPHDataProvider
     */
    public function testIdPH($case, $expectedResult)
    {
        @$this->_getDB()->query('?#', $case);
        $this->assertEquals($expectedResult, $this->_getDB()->getLastQuery());
    }

    abstract public function stringPHDataProvider();
    abstract public function digitPHDataProvider();
    abstract public function floatPHDataProvider();
    abstract public function linkPHDataProvider();
    abstract public function listPHDataProvider();
    abstract public function hashPHDataProvider();
    abstract public function idPHDataProvider();
}
