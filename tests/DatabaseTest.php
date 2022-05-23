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
     * Name for Database connection to use for tests
     */
    protected $dbConnectionName = 'temp';

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
     * @covers \PHPCore\Database
     * @depends testDatabaseClassExists
     * @dataProvider dataProviderMethodsExist
     */
    public function testMethodsExist(string $method): void
    {
        $this->assertTrue(
            method_exists('\PHPCore\Database', $method),
            "Method `$method` was not found in the Database class."
        );
    }

    /**
     * @covers ::parse_dsn
     * @depends testDatabaseClassExists
     */
    public function testDatabaseConfig(): void
    {
        $path = realpath( __DIR__ . DIRECTORY_SEPARATOR . '../config.json' );
        $config = json_decode(file_get_contents($path));
        $this->assertNotNull(
            $config,
            "PHPCore config missing or not valid. ($path)"
        );
        $this->assertTrue(
            isset($config->database),
            "PHPCore config missing database parameter. ($path)"
        );
        $this->assertTrue(
            isset($config->database->connections),
            "PHPCore database config missing connections parameter. ($path)"
        );
        $connection = null;
        $connName = $this->dbConnectionName;
        foreach ($config->database->connections as $connItem) {
            if (isset($connItem->name) === false) {
                continue;
            }
            if ($connName === $connItem->name) {
                $connection = $connItem;
            }
        }
        $this->assertIsObject(
            $connection,
            "Database connection `$connName` was not found in PHPCore config. ($path)"
        );
        $this->assertTrue(
            isset($connection->dsn),
            "Database connection `$connName` is missing the dsn parameter. ($path)"
        );
        $dsn = parse_dsn($connection->dsn);
        $required_params = ['usr', 'pwd'];
        foreach ($required_params as $index) {
            $this->assertObjectHasAttribute(
                $index,
                $connection,
                "Database connection `$connName` is missing the `$index` parameter. ($path)"
            );
        }
    }

    /**
     * @covers \PHPCore\Database::__construct
     * @covers ::parse_dsn
     * @depends testDatabaseClassExists
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
     * @depends testDatabaseConfig
     */
    public function testValidConstruct(): void
    {
        $this->assertInstanceOf(
            Database::class,
            new Database(
                $this->getDbConnectionConfig('dsn'),
                $this->getDbConnectionConfig('usr'),
                $this->getDbConnectionConfig('pwd')
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
            $this->getDbConnectionConfig('dsn'),
            $this->getDbConnectionConfig('usr'),
            $this->getDbConnectionConfig('pwd')
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

    /**
     * @covers \PHPCore\Database::createRecord
     * @covers \PHPCore\Database::getLastInsertId
     * @depends testGetInstance
     * @dataProvider dataProviderCreateRecord
     */
    public function testCreateRecord(string $table, array $data): void
    {
        
        echo "\n\n";
        var_dump($_CORE);
        var_dump($GLOBALS['_CORE']);
        echo "\n\n";        
        
        // TODO: Have not built this method yet
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertIsInt(
            Database::getInstance()->createRecord($table, $data)
        );
    }

    // -----------------------------------------------------------------------------------------

    public function dataProviderMethodsExist(): array
    {
        return [
            // Mthod names
            ['__construct'],
            ['getInstance'],
            ['createRecord'],
            ['getLastInsertId'],
            ['getRowsAffected'],
        ];
    }

    public function dataProviderCreateRecord(): array
    {
        return [
            // table        data
            ['User',        ['John', 'Doe']],
            ['UserAccess',  [1, 'Guest']],
        ];
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Helper method to get Database Connection Config
     */
    protected function getDbConnectionConfig(string $key = null, string $connName = null): mixed
    {
        if (isset($this->databaseConfig) === false) {
            $path = realpath( __DIR__ . DIRECTORY_SEPARATOR . '../config.json' );
            $this->databaseConfig = json_decode(file_get_contents($path))->database;
        }
        $connection = null;
        $connName = $connName || $this->dbConnectionName;
        foreach ($this->databaseConfig->connections as $connItem) {
            if (isset($connItem->name) === false) {
                continue;
            }
            if ($connItem->name == $connName) {
                $connection = $connItem;
            }
        }
        return $connection->$key;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////