<?php

require_once 'PHPUnit/Framework.php';
require_once 'DB/Adapter/Factory.php';

abstract class DB_Adapter_AbstractTest extends PHPUnit_Framework_TestCase
{
    protected $_DB;
    protected $_dbtype;
    protected $_testUsers = array(
        array(
            'id' => 1,
            'login' => 'vb',
            'mail' => 'vb@in-source.ru',
            'age' => 20,
            'active' => 0
        ),
        array(
            'id' => 2,
            'login' => 'pavel',
            'mail' => 'example-pavel@gmail.com',
            'age' => 24,
            'active' => 1
        ),
    );

    public function setUp()
    {
        $this->_connect();
        $this->_createTestTables();
    }

    public function testConnectionSucceeded()
    {
        $this->assertNotNull($this->_DB);
    }

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

    abstract function digitPHDataProvider();

    /**
     * @dataProvider floatPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testFloatPH($case, $expectedResult)
    {
        @$this->_DB->query('?f', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function floatPHDataProvider();

    /**
     * @dataProvider linkPHDataProvider
     * @depends testConnectionSucceeded
     */
    public function testLinkPH($case, $expectedResult)
    {
        @$this->_DB->query('?n', $case);
        $this->assertEquals($expectedResult, $this->_DB->getLastQuery());
    }

    abstract function linkPHDataProvider();

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
    
    public function testConnectionFailed()
    {
        $this->setExpectedException('DB_Adapter_Exception_ConnectionError');
        $failed = DB_Adapter_Factory::connect('mysql://not_existed:test@localhost/test?charset=utf8');
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testGetLastQuery()
    {
        $this->_DB->query('SELECT * FROM test_user');
        $this->assertEquals('SELECT * FROM test_user', $this->_DB->getLastQuery());

        @$this->_DB->query('BAD QUERY');
        $this->assertEquals('BAD QUERY', $this->_DB->getLastQuery());
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testGetLastQueryInline()
    {
        $this->_DB->query("\t\t\t SELECT * \n FROM  test_user");
        $this->assertEquals('SELECT * FROM test_user', $this->_DB->getLastQuery($inline = true));
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testQueryError()
    {
        $this->setExpectedException('DB_Adapter_Exception_QueryError');
        $this->_DB->query("SOME BAD QUERY");
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testBlob()
    {
        $b = $this->_DB->blob();
        $this->assertTrue(!is_null($b));
        $this->assertTrue($b instanceof DB_Adapter_Generic_Blob);
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testIdentPrefixCorrect()
    {
        $this->assertEquals($this->_DB->setIdentPrefix(), 'test_');
    }

    /**
     * @depends testConnectionSucceeded
     */
    public function testIdentPrefixPH()
    {
        @$this->_DB->select("SELECT * FROM ?_user");
        $this->assertEquals($this->_DB->getLastQuery(), "SELECT * FROM test_user");
    }

    /**
     * @depends testConnectionSucceeded
     * @depends testIdentPrefixPH
     */
    public function testCommonFunctionality()
    {
        $this->_createTestTables();
        foreach ($this->_testUsers as $u) {
            $this->_DB->query("
                INSERT INTO ?_user
                VALUES (?a)",
                array_values($u)
            );
        }

        $this->assertEquals(
            $this->_DB->select("SELECT * FROM ?_user"),
            $this->_testUsers
        );
    }

    /**
     * @depends testConnectionSucceeded
     * @depends testIdentPrefixPH
     * @dataProvider conditionsProvider
     */
    public function testConditionalPH($active, $query)
    {
        $this->_DB->select("
            SELECT * FROM ?_user
          { WHERE active = ?d }",
            is_null($active) ? DB_ADAPTER_SKIP : $active
        );

        $this->assertEquals($query, $this->_DB->getLastQuery($inline = true));
    }

    public function conditionsProvider()
    {
        return array(
            array(null, 'SELECT * FROM test_user'),
            array(1, 'SELECT * FROM test_user WHERE active = 1'),
        );
    }

    protected function _connect()
    {
        if (empty(TestHelper::$dbInstances[$this->_dbtype])) {
            TestHelper::$dbInstances[$this->_dbtype] = DB_Adapter_Factory::connect(
                TestHelper::$dsn[$this->_dbtype]
            );
        }

        $this->_DB = TestHelper::$dbInstances[$this->_dbtype];
    }

    protected function _createTestTables()
    {
        @$this->_DB->query("DROP TABLE test_user");
        @$this->_DB->query("DROP TABLE test_tree");

        $this->_DB->query("
            CREATE TABLE test_user (
                id     int(11)      NOT NULL  auto_increment,
                login  varchar(100) NOT NULL,
                mail   varchar(400) NOT NULL,
                age    int(11)      NOT NULL,
                active boolean      DEFAULT FALSE NOT NULL,
                PRIMARY KEY (id)
            )
            ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
        );
    }
}