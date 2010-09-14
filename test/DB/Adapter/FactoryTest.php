<?php

require_once 'PHPUnit/Framework.php';
require_once 'DB/Adapter/Factory.php';

/**
 * @group Generic
 * @group All
 */
class DB_Adapter_FactoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dsnProvider
     */
    public function testParseDSNCommon($dsn, $parsed)
    {
        $this->assertEquals($parsed, DB_Adapter_Factory::parseDSN($dsn));
    }

    /**
     * @dataProvider dsnProviderBad
     */
    public function testParseDSNBad($dsn)
    {
        // we must parse bad dsns silently.
        // Error here - its DBMS problem
        DB_Adapter_Factory::parseDSN($dsn);
    }

    public function dsnProviderBad()
    {
        return array(
            array('i am bad'),
            array('')
        );
    }

    public function dsnProvider()
    {
        return array(
            // common dsn
            array(
                'dbtype://username:pass@dbhost/dbname?param=val',
                array(
                    'scheme' => 'dbtype',
                    'host' => 'dbhost',
                    'user' => 'username',
                    'pass' => 'pass',
                    'path' => '/dbname',
                    'query' => 'param=val',
                    'param' => 'val',
                    'dsn' => 'dbtype://username:pass@dbhost/dbname?param=val',
                )
            ),
            // dsn with @ simbol in password
            array(
                'dbtype://username:@pas@s@dbhost/dbname?param=val',
                array(
                    'scheme' => 'dbtype',
                    'host' => 'dbhost',
                    'user' => 'username',
                    'pass' => '@pas@s',
                    'path' => '/dbname',
                    'query' => 'param=val',
                    'param' => 'val',
                    'dsn' => 'dbtype://username:@pas@s@dbhost/dbname?param=val',
                )
            ),
            // dsn with specified port
            array(
                'dbtype://username:pass@dbhost:1234/dbname?param=val',
                array(
                    'scheme' => 'dbtype',
                    'host' => 'dbhost',
                    'user' => 'username',
                    'port' => 1234,
                    'pass' => 'pass',
                    'path' => '/dbname',
                    'query' => 'param=val',
                    'param' => 'val',
                    'dsn' => 'dbtype://username:pass@dbhost:1234/dbname?param=val',
                )
            ),
            // already parsed dsn
            array(
                array('bla' => 'bla'),
                array('bla' => 'bla')
            )
        );
    }
}
