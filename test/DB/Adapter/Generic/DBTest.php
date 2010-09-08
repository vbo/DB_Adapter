<?php
require_once 'PHPUnit/Framework.php';
require_once 'DB/Adapter/Factory.php';

class DB_Adapter_GenericTest extends PHPUnit_Framework_TestCase
{
    public $DB;
    public $users_tbl_data = array(
        array(
            'id'    => 1,
            'login' => 'vb',
            'mail'  => 'vb@in-source.ru',
            'age'   => 20,
        ),
        array(
            'id'    => 2,
            'login' => 'pavel',
            'mail'  => 'example-pavel@gmail.com',
            'age'   => 24,
        ),
    );

    function setUp ()
    {
        $this->connect();
        $this->createTestTables();
    }

    function connect ()
    {
        $this->DB = DB_Adapter_Factory::connect(
            'mysql://insourceru_dev:vb31337@localhost/insourceru_dev?charset=utf8&ident_prefix=test_'
        );
    }

    function createTestTables ()
    {
        @$this->DB->query("DROP TABLE test_users");
        @$this->DB->query("DROP TABLE test_tree");

        $this->DB->query("
            CREATE TABLE test_users (
                id     int(11)      NOT NULL  auto_increment,
                login  varchar(100) NOT NULL,
                mail   varchar(400) NOT NULL,
                age    int(11)      NOT NULL,
                PRIMARY KEY (id)
            )
            ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
        );
    }

    function testConnectionSucceeded ()
    {
        $this->assertNotNull($this->DB);
    }

    function testConnectionFailed ()
    {
        $this->setExpectedException('DB_Adapter_Exception_ConnectionError');
        $failed = DB_Adapter_Factory::connect('mysql://not_existed:test@localhost/test?charset=utf8');
    }

    function testIdentPrefixCorrect ()
    {
        $this->assertEquals($this->DB->setIdentPrefix(), 'test_');
    }

    function testIdentPrefixPH ()
    {
        @$this->DB->select("SELECT * FROM ?_users");
        $this->assertEquals($this->DB->getLastQuery(), "SELECT * FROM test_users");
    }

    function testListPH ()
    {
        $this->createTestTables();

        foreach($this->users_tbl_data as $u)
        {
            $this->DB->query("
                INSERT INTO ?_users
                VALUES (?a)",

                array_values($u)
            );
        }

        $this->assertEquals(
            $this->DB->select("SELECT * FROM ?_users"),
            $this->users_tbl_data
        );
    }
}