<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Database
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;
use PHPCore\Database;

/**
 * Database Test Fixture
 *
 * This test fixture is uses to the Database class.
 */
final class DatabaseTest extends TestCase
{
    /**
     * Holder for Database config from JSON
     */
    protected $databaseConfig = null;

    // -----------------------------------------------------------------------------------------

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
     * @covers \PHPCore\Database
     */
    public function testDatabaseClassExists(): void
    {
        $this->assertTrue(
            class_exists('\PHPCore\Database'), 
            'Database class does not exist'
        );
    }


    /**
     * @covers ::parse_dsn
     * $depends testDatabaseClassExists
     */
    public function testDatabaseConfig(): void
    {
        $path = realpath( __DIR__ . DIRECTORY_SEPARATOR . '../config.json' );
        $config = json_decode(file_get_contents($path));
        $this->assertNotNull(
            $config,
            "Config JSON missing or not valid. ($path)"
        );

        $this->assertTrue(
            isset($config->database),
            "Config JSON missing database parameter. ($path)"
        );
        foreach (['dsn', 'usr', 'pwd'] as $index) {
            $this->assertTrue(
                isset($config->database->$index),
                "Config JSON missing database:$index parameter. ($path)"
            );
        }
    }

    /**
     * @covers \PHPCore\Database::__construct
     * @covers ::parse_dsn
     * $depends testDatabaseClassExists
     */
    public function testInvalidConstruct(): void
    {
        $this->expectException(PDOException::class);
        $dsn_str = 'mysql:host=localhost;dbname=my_database;charset=utf8mb4';
        $usr = 'unknown-user';
        $pwd = 'unknown-password';
        $db = new Database($dsn_str, $usr, $pwd, 'invalid');
    }

    /**
     * @covers \PHPCore\Database::__construct
     * @covers ::parse_dsn
     * $depends testDatabaseConfig
     */
    public function testValidConstruct(): void
    {
        $this->assertInstanceOf(
            Database::class,
            new Database(
                $this->getDatabaseConfig('dsn'),
                $this->getDatabaseConfig('usr'),
                $this->getDatabaseConfig('pwd')
            )
        );
    }

    /**
     * @covers \PHPCore\Database::__construct
     * @covers ::parse_dsn
     * @depends testValidConstruct
     */
    public function testDoubleConstruct(): void
    {
        $this->expectException(Exception::class);
        new Database(
            $this->getDatabaseConfig('dsn'),
            $this->getDatabaseConfig('usr'),
            $this->getDatabaseConfig('pwd')
        );
    }
    /**
     * @covers \PHPCore\Database::getInstance
     * @depends testValidConstruct
     */
    public function testGetInstance(): void
    {
        $this->assertInstanceOf(
            Database::class,
            Database::getInstance()
        );
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Helper method to get Database Config
     */
    protected function getDatabaseConfig(string $key = null): mixed
    {
        if (isset($this->databaseConfig) === false) {
            $path = realpath( __DIR__ . DIRECTORY_SEPARATOR . '../config.json' );
            $this->databaseConfig = json_decode(file_get_contents($path))->database;
            
        }
        return $this->databaseConfig->$key;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////