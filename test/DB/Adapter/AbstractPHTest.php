<?php

require_once 'PHPUnit/Framework.php';

abstract class DB_Adapter_AbstractPHTest extends PHPUnit_Framework_TestCase
{    
    /**
     * @dataProvider stringPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testStringPH($case, $expectedResult)
    {
        @$this->_DB->query('?', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function stringPHDataProvider()
    {
        return array();
    }

    /**
     * @dataProvider digitPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testDigitPH($case, $expectedResult)
    {
        @$this->_DB->query('?d', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function digitPHDataProvider()
    {
        return array();
    }

    /**
     * @dataProvider floatPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testFloatPH($case, $expectedResult)
    {
        @$this->_DB->query('?f', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function floatPHDataProvider()
    {
        return array();
    }

    /**
     * @dataProvider linkPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testLinkPH($case, $expectedResult)
    {
        @$this->_DB->query('?n', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function linkPHDataProvider()
    {
        return array(
            array(1, "1"),
            array(null, "NULL"),
        );
    }

    /**
     * @dataProvider listPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testListPH($case, $expectedResult)
    {
        @$this->_DB->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function listPHDataProvider()
    {
        return array();
    }

    /**
     * @dataProvider hashPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testHashPH($case, $expectedResult)
    {
        @$this->_DB->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function hashPHDataProvider()
    {
        return array();
    }

    /**
     * @dataProvider idPHDataProvider
     * @depends testConnectionSucceeded
     * @depends testGetLastQuery
     */
    public function testIdPH($case, $expectedResult)
    {
        @$this->_DB->query('?#', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function idPHDataProvider()
    {
        return array();
    }
}
