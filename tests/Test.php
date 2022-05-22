<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Global
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;

/**
 * Global Test Fixture
 *
 * This test fixture is uses to test global functions.
 */
final class Test extends TestCase
{
    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * begining of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        // Place Holder
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        // Place Holder
    }
  
    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {
        // Place Holder
    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {
        // Place Holder
    }

    // -----------------------------------------------------------------------------------------

    /**
     * @covers ::parse_dsn
     */
    public function testParseDsnFunctionExists(): void
    {
        $this->assertTrue(
            function_exists('parse_dsn'), 
            'parse_dsn() function does not exist'
        );
        
    }

    /**
     * @covers ::parse_dsn
     * @dataProvider dataProvidertestParseDsn
     */
    public function testParseDsn(string $dsn, array $validResp): void
    {
        $this->assertEquals(
            parse_dsn($dsn), $validResp
        );
    }

    /**
     * @covers ::parse_dsn
     */
    public function testParseDsnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $dsn_str = 'host=localhost;dbname=my_database;charset=utf8mb4';
        parse_dsn($dsn_str);
    }

    // -----------------------------------------------------------------------------------------

    public function dataProvidertestParseDsn(): array
    {
        return [
            [
                'sqlite:',
                [
                    'driver'  => 'sqlite',
                ]
            ],
            [
                'sqlite:/opt/databases/mydb.sq3',
                [
                    'driver'  => 'sqlite',
                    'path'    => '/opt/databases/mydb.sq3',
                ]
            ],
            [
                'sqlite::memory:',
                [
                    'driver'  => 'sqlite',
                ]
            ],
            [
                'mysql:host=localhost;dbname=my_database;charset=utf8mb4',
                [
                    'driver'  => 'mysql',
                    'host'    => 'localhost',
                    'dbname'  => 'my_database',
                    'charset' => 'utf8mb4',
                ]
            ],
        ];
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////