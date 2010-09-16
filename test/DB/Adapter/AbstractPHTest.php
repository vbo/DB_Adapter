<?php

require_once 'PHPUnit/Framework.php';

abstract class DB_Adapter_AbstractPHTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testStringPH($case, $expectedResult)
    {
        @$this->_DB->query('?', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function stringPHDataProvider();

    /**
     * @dataProvider digitPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testDigitPH($case, $expectedResult)
    {
        @$this->_DB->query('?d', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function digitPHDataProvider()
    {
        return array(
            array('1', "1"),
            array('1a', "1"),
            array(1, "1"),
            array(null, "NULL"),
        );
    }

    /**
     * @dataProvider floatPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testFloatPH($case, $expectedResult)
    {
        @$this->_DB->query('?f', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    public function floatPHDataProvider()
    {
        return array(
            array(1, "1"),
            array(1.5, "1.5"),
            array(1.5, "1.5"),
            array(null, "NULL"),
        );
    }

    /**
     * @dataProvider linkPHDataProvider
     * @depends testConnectionSucceeded
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
     */
    public function testListPH($case, $expectedResult)
    {
        @$this->_DB->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function listPHDataProvider();

    /**
     * @dataProvider hashPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testHashPH($case, $expectedResult)
    {
        @$this->_DB->query('?a', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function hashPHDataProvider();

    /**
     * @dataProvider idPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testIdPH($case, $expectedResult)
    {
        @$this->_DB->query('?#', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function idPHDataProvider();
}
