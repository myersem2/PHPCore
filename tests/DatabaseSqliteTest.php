<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Database SQLite Driver
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
final class DatabaseSqliteTest extends TestCase
{
    /**
     * @var array $_CORE_INI Backup
     */
    static $core_ini_bkup = [];

    /**
     * Database config to be used for testing
     */
    static $databaseConfig = [
        'main.dsn' => 'sqlite:/tmp/temp_phpunit.db',
        'main.usr' => '',
        'main.pwd' => '',
        'alt1.dsn' => 'sqlite:',
        'alt1.usr' => '',
        'alt1.pwd' => '',
        'alt1.name' => 'backup',
        'alt2.dsn' => 'sqlite:',
        'alt2.usr' => '',
        'alt2.pwd' => '',
        'alt2.name' => 'backup2',
        'alt3.dsn' => 'sqlite:',
        'alt3.usr' => '',
        'alt3.pwd' => '',
        'alt3.name' => 'main2',
    ];

    // -----------------------------------------------------------------------------------------

    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        Database::unlinkInstances();
        self::$core_ini_bkup = $GLOBALS['_CORE_INI'];
        $GLOBALS['_CORE_INI']['Database'] = self::$databaseConfig;
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        $GLOBALS['_CORE_INI'] = self::$core_ini_bkup;
    }

    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {
        list($setUp) = explode(' ', 'setUp' . substr($this->getName(), 4));
        if (method_exists($this, $setUp) === true) {
            $this->$setUp();
        }
    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {
        list($tearDown) = explode(' ', 'tearDown' . substr($this->getName(), 4));
        if (method_exists($this, $tearDown) === true) {
            $this->$tearDown();
        }
    }

    // -----------------------------------------------------------------------------------------

    /**
     * @coversNothing
     */
    public function testDatabaseClassExists(): void
    {
        $this->assertTrue(
            class_exists('\PHPCore\Database'), 
            'Database class does not exist'
        );
    }

    /**
     * @coversNothing
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
    public function dataProviderMethodsExist(): array
    {
        return [
            ['__getInstance'],
            ['__construct'],
            ['createRecord'],
            ['deleteRecord'],
            ['deleteRecords'],
            ['getRecord'],
            ['getRecords'],
            ['getSchema'],
            ['getTableSchema'],
            ['getRecords'],
            ['exec'],
            ['getLastExecException'],
            ['getLastInsertId'],
            ['getRowsAffected'],
            ['procedure'],
            ['query'],
            ['updateRecord'],
            ['updateRecords'],
        ];
    }

    /**
     * @covers \PHPCore\Database::__construct
     * @covers ::core_ini_get_all
     * @covers ::parse_dsn
     * @depends testMethodsExist
     * @dataProvider dataProviderConstruct
     */
    public function testConstruct(string $name, string $dsn, string|null $usr, string|null $pwd, string $assert): void
    {
        if ($assert == 'Exception') {
            $this->expectException(Exception::class);
        }
        if ($assert == 'PDOException') {
            $this->expectException(PDOException::class);
        }
        $db = new Database($name, $dsn, $usr, $pwd);
        $this->assertInstanceOf(
            Database::class,
            $db
        );
        $this->expectException(Exception::class);
        $db = new Database($name, $dsn, $usr, $pwd);
    }
    private function dataProviderConstruct(): array
    {
        
        return [
            [
                'main',
                '',
                null,
                null,
                'Success',
            ],
            [
                self::$databaseConfig['alt1.name'],
                self::$databaseConfig['alt1.dsn'],
                self::$databaseConfig['alt1.usr'],
                self::$databaseConfig['alt1.pwd'],
                'Success',
            ],
            [
                'no_good',
                '',
                '',
                '',
                'PDOException',
            ],
            [
                self::$databaseConfig['alt2.name'],
                '',
                null,
                null,
                'Success',
            ],
            [
                'driver-not-supported',
                'pgsql:',
                '',
                '',
                'PDOException',
            ],
        ];
    }

    /**
     * @covers \PHPCore\Database::__getInstance
     * @covers \PHPCore\Database::__construct
     * @covers ::core_ini_get_all
     * @covers ::parse_dsn
     * @depends testConstruct
     * @dataProvider dataProviderGetInstance
     */
    public function testGetInstance(string|null $name, string $assert): void
    {
        if ($assert == 'Exception') {
            $this->expectException(Exception::class);
        }
        if (empty($name) === true) {
            $this->assertInstanceOf(
                Database::class,
                Database::__getInstance()
            );
        } else {
            $this->assertInstanceOf(
                Database::class,
                Database::__getInstance($name)
            );
        }
    }
    private function dataProviderGetInstance(): array
    {
        return [
            [ null,      'Success'   ],
            [ '_bad',    'Exception' ],
            [ 'main',    'Success'   ],
            [ 'alt1',    'Exception' ],
            [ 'backup',  'Success'   ],
            [ 'alt2',    'Exception' ],
            [ 'unknown', 'Exception' ],
            [ 'main2',   'Success'   ],
        ];
    }

    /**
     * @covers \PHPCore\Database::createRecord
     * @covers \PHPCore\Database::createRecords
     * @covers \PHPCore\Database::__getInstance
     * @covers \PHPCore\Database::exec
     * @covers \PHPCore\Database::query
     * @covers \PHPCore\Database::_execStatement
     * @covers \PHPCore\Database::_cleanName
     * @covers \PHPCore\Database::getLastExecException
     * @covers \PHPCore\Database::getLastInsertId
     * @covers \PHPCore\Database::getRowsAffected
     * @depends testGetInstance
     * @dataProvider dataProviderCreateRecords
     */
    public function testCreateRecords(string $table, string $type, array $data, int $flags, array $tests): void
    {
        $db = Database::__getInstance();
        $res = $db->exec("DELETE FROM `$table`");
        if (isset($tests['pre_query'])) {
            $result = $db->exec($tests['pre_query'], [], Database::RETURN_LAST_INSERT_ID);
            $this->assertIsInt($result, "Pre Query Failed");
            $this->assertGreaterThan(0, $result, "Pre Query Failed");
        }

        if ($tests['assert'] === 'Exception') {
            $this->expectException(Exception::class);
        }
        if ($tests['assert'] === 'PDOException') {
            $this->expectException(PDOException::class);
        }

        $onUpdate = $tests['onUpdate'] ?? null;
        $key = $tests['key'] ?? null;

        switch ($type) {
            case 'single':
                $result = $db->createRecord($table, $data, $onUpdate, $flags, $key);
            break;
            case 'multiple':
                $result = $db->createRecords($table, $data, $onUpdate, $flags, $key);
            break;
        }

        $debug_output = '';
        if ($flags & Database::DEBUG_MODE) {
            $type_val = gettype($result) . "($result)";
            $debug_output = "$type_val was returned.\n" . print_r($db->DebugData, true);
        }

        switch ($tests['assert']) {
            case 'ID':
                $debug_output = "Response was not an integer $debug_output";
                $this->assertIsInt($result, $debug_output);
                $this->assertGreaterThan(0, $result);
            break;
            case 'True':
                $debug_output = "Response was not a TRUE boolean $debug_output";
                $this->assertTrue($result, $debug_output);
                $records = $db->query("SELECT * FROM `$table`", [], Database::RETURN_FIRST_RESULT_ONLY);
                $this->assertNotEmpty($records);
            break;
            case 'Updated':
                $debug_output = "Response was not a TRUE boolean $debug_output";
                $this->assertTrue($result, $debug_output);
                $record = $db->query("SELECT * FROM `$table`", [], Database::RETURN_FIRST_RESULT_ONLY);
                $this->assertEquals(
                    $record->Email,
                    $onUpdate['Email']
                );
            break;
            case 'False':
                $debug_output = "Response was not a FALSE boolean $debug_output";
                $this->assertFalse($result, $debug_output);
            break;
            case 'False-Exception':
                $this->assertFalse($result, $debug_output);
                $this->assertEquals(
                    $db->getLastExecException()->getCode(),
                    'HY000',
                );
            break;
            Default:
                echo "\nResponse was not an exception {$tests['assert']} $debug_output\n";
            break;
        }
        
        if (isset($tests['primary_key']) and $type == 'single') {
            $record = $db->query(
                "SELECT * FROM `$table` WHERE `{$tests['primary_key']}` = :primary_key",
                [ 'primary_key' => $result],
                Database::RETURN_FIRST_RESULT_ONLY
            );
            $check_array = [];
            foreach ($data as $column=>$value) {
                $check_array[$column] = $record->$column ?? null;
            }
            $this->assertEquals($data, $check_array);
        }

        if (isset($tests['match_column']) and $type == 'multiple') {
            $col = $tests['match_column'];
            $records = $db->query("SELECT * FROM `$table`");
            foreach ($data as $data_set) {
                $check_array = [];
                foreach ($data_set as $column=>$value) {
                    foreach ($records as $record) {
                        if (empty($record->$col) or $record->$col !== $data_set[$col]) continue;
                        $check_array[$column] = $record->$column ?? null;
                    }
                }
                $this->assertEquals($data_set, $check_array);
            }
        }

    }
    private function setUpCreateRecords(): void
    {
        $db = Database::__getInstance();
        $db->exec('DROP TABLE IF EXISTS `User`');
        $db->exec('DROP TABLE IF EXISTS `UserAccess`');
        $db->exec('
            CREATE TABLE IF NOT EXISTS `User` (
              `UserId` INTEGER PRIMARY KEY,
              `Name` TEXT NULL,
              `Email` TEXT NULL,
              `Phone` TEXT NULL,
              UNIQUE(`Name`)
            )
        ');

        $db->exec('
            CREATE TABLE `UserAccess` (
              `UserId` INTEGER NOT NULL,
              `Access` TEXT NOT NULL,
              CONSTRAINT `UserIdAccess` PRIMARY KEY (`UserId`, `Access`)
            )
        ');
    }
    private function dataProviderCreateRecords(): array
    {
        return [
            [ // #0 Empty Record
                'User',
                'single',
                [],
                0,
                [ 'assert' => 'Exception' ],
            ],
            [ // #1 Unknown Column (Return Boolean)
                'User',
                'single',
                [ 'UnknownColumn' => 'John Doe' ],
                Database::RETURN_BOOL,
                [ 'assert' => 'False' ],
            ],
            [ // #2 Unknown Column (No Throw Flag)
                'User',
                'single',
                [ 'UnknownColumn' => 'John Doe' ],
                Database::NO_THROW_ON_ERROR,
                [ 'assert' => 'False-Exception' ],
            ],
            [ // #3 Unknown Column (Throw PDOException)
                'User',
                'single',
                [ 'UnknownColumn' => 'John Doe' ],
                0,
                [ 'assert' => 'PDOException' ],
            ],
            [ // #4 Multiple BLANK Records (Throw Exception)
                'User',
                'multiple',
                [
                    [ ],
                    [ ],
                ],
                0,
                [ 'assert' => 'Exception' ],
            ],
            [ // #5 Multiple Records Column Count Mismatch (Throw Exception)
                'User',
                'multiple',
                [
                    [ 'Name' => 'John Doe' ],
                    [ 'Name' => 'Jane Doe', 'Email' => 'test@domain.com' ],
                ],
                0,
                [ 'assert' => 'Exception' ],
            ],
            [ // #6 Normal Insert Record
                'User',
                'single',
                [ 'Name' => 'John Doe' ],
                0,
                [
                    'assert' => 'ID',
                    'primary_key' => 'UserId',
                ],
            ],
            [ // #7 Multiple Records and Insert Ignore
                'User',
                'multiple',
                [
                    [ 'Name' => 'John Doe' ],
                    [ 'Name' => 'Jane Doe' ],
                ],
                Database::INSERT_IGNORE,
                [
                    'assert' => 'True',
                    'match_column' => 'Name',
                ],
            ],
            [ // #8 Insert On Duplicate Update
                'User',
                'single',
                [ 'Name' => 'John Doe', 'Email' => 'updated@domain.com' ],
                Database::ON_DUPLICATE_UPDATE,
                [
                    'assert' => 'Exception',
                    'pre_query' => "INSERT INTO `User` (`Name`, `Email`) VALUES ('John Doe', 'no-email@domain.com')",
                ],
            ],
            [ // #9 Insert On Duplicate Update
                'User',
                'single',
                [ 'Name' => 'John Doe', 'Email' => 'updated@domain.com' ],
                Database::RETURN_BOOL,
                [
                    'assert' => 'Exception',
                    'onUpdate' => [ 'Email' => 'updated@domain.com' ],
                    'pre_query' => "INSERT INTO `User` (`Name`, `Email`) VALUES ('John Doe', 'no-email@domain.com')",
                ],
            ],
            [ // #10 Insert On Duplicate Update
                'User',
                'single',
                [ 'Name' => 'John Doe', 'Email' => 'updated@domain.com' ],
                Database::RETURN_BOOL | Database::ON_DUPLICATE_UPDATE,
                [
                    'assert' => 'Exception',
                    'onUpdate' => [ 'Email' => 'updated@domain.com' ],
                    'pre_query' => "INSERT INTO `User` (`Name`, `Email`) VALUES ('John Doe', 'no-email@domain.com')",
                ],
            ],
            [ // #11 Insert On Duplicate Update
                'User',
                'single',
                [ 'Name' => 'John Doe', 'Email' => 'updated1@domain.com' ],
                Database::RETURN_BOOL | Database::ON_DUPLICATE_UPDATE,
                [
                    'assert' => 'Updated',
                    'onUpdate' => [
                        'Email' => 'updated@domain.com',
                        'Phone' => '867-5309',
                    ],
                    'pre_query' => "INSERT INTO `User` (`Name`, `Email`) VALUES ('John Doe', 'no-email@domain.com')",
                    'key' => 'Name',
                ],
            ],
            [ // #12 Insert Composite Key
                'UserAccess',
                'single',
                [ 'UserId' => 1, 'Access' => 'Guest'],
                // NOTE: leave DEBUG_MODE on here so it can be tested as well
                Database::DEBUG_MODE | Database::RETURN_BOOL,
                [
                    'assert' => 'True',
                ],
            ],
            [ // #13 Unknown Column (Throw PDOException)
                'User',
                'single',
                [ 'UnknownColumn' => 'John Doe' ],
                Database::DEBUG_MODE | Database::RETURN_BOOL,
                [ 'assert' => 'False' ],
            ],
        ];
    }
    private function tearDownCreateRecords(): void
    {
        $db = Database::__getInstance();
        $db->exec('DROP TABLE IF EXISTS `User`');
        $db->exec('DROP TABLE IF EXISTS `UserAccess`');
    }

    /**
     * @covers \PHPCore\Database::getRecord
     * @covers \PHPCore\Database::getRecords
     * @covers \PHPCore\Database::__getInstance
     * @covers \PHPCore\Database::exec
     * @covers \PHPCore\Database::query
     * @covers \PHPCore\Database::_execStatement
     * @covers \PHPCore\Database::_cleanName
     * @covers \PHPCore\Database::_buildOrderByLimit
     * @covers \PHPCore\Database::_buildWhere
     * @covers \PHPCore\Database::getLastExecException
     * @covers \PHPCore\Database::getLastInsertId
     * @covers \PHPCore\Database::getRowsAffected
     * @depends testGetInstance
     * @dataProvider dataProviderGetRecords
     */
    public function testGetRecords(string $type, array $where, array|null $order, int|null $limit, int|null $offest, array|null $match, int $flags = 0): void
    {
        $db = Database::__getInstance();
        $this->setUpGetRecords(false);
        $res = $db->query("SELECT * FROM `User`");
        if (count($res) !== 4) {
            $this->markTestSkipped('setUpGetRecords() did not setup database correctly');
            return;
        }

        switch ($type) {
            case 'single':
                $result = $db->getRecord('User', $where, $flags);
            break;
            case 'multiple':
                $result = $db->getRecords('User', $where, $order, $limit, $offest, $flags);
            break;
        }

        $debug_output = '';
        if ($flags & Database::DEBUG_MODE) {
            $type_val = gettype($result);
            $debug_output = "$type_val was returned.\n" . print_r($db->DebugData, true);
        }

        $debug_output = "Records removed to not matach expected count $debug_output";
        $result_clean = json_encode($result);
        $this->assertEquals($result_clean, json_encode($match), $debug_output);

    }
    private function setUpGetRecords(bool $createTable = true): void
    {
        $db = Database::__getInstance();
        if ($createTable) {
            $db->exec('DROP TABLE IF EXISTS `User`');
            $db->exec('
                CREATE TABLE IF NOT EXISTS `User` (
                  `UserId` INTEGER PRIMARY KEY,
                  `Name` TEXT NULL,
                  `Email` TEXT NULL,
                  UNIQUE(`Name`)
                )
            ');
        } else {
            $db->exec("DELETE FROM `User`");
        }
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('John', 'john@email.com')");
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('Jane', 'john@email.com')");
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('Julie', 'jim@email.com')");
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('Jim', 'jim@email.com')");
    }
    private function dataProviderGetRecords(): array
    {
        return [
            [ // #0 Single Record
                'single',
                [ 'UserId' => 1 ],
                null, null, null,
                [
                    'UserId' => 1,
                    'Name' => 'John',
                    'Email' => 'john@email.com',
                ],
                Database::DEBUG_MODE
            ],
            [ // #1 Mutiple Records
                'multiple',
                [ 'Email' => 'john@email.com' ],
                null, null, null,
                [
                    [
                        'UserId' => 1,
                        'Name' => 'John',
                        'Email' => 'john@email.com',
                    ],
                    [
                        'UserId' => 2,
                        'Name' => 'Jane',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #2 Mutiple Records Order
                'multiple',
                [ 'Email' => 'john@email.com' ],
                ['Name'], null, null,
                [
                    [
                        'UserId' => 2,
                        'Name' => 'Jane',
                        'Email' => 'john@email.com',
                    ],
                    [
                        'UserId' => 1,
                        'Name' => 'John',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #3 Mutiple Records Order
                'multiple',
                [ 'Email' => 'john@email.com' ],
                ['Name DESC'], null, null,
                [
                    [
                        'UserId' => 1,
                        'Name' => 'John',
                        'Email' => 'john@email.com',
                    ],
                    [
                        'UserId' => 2,
                        'Name' => 'Jane',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #4 Mutiple Records Order
                'multiple',
                [ ],
                ['Email','Name DESC'], null, null,
                [
                    [
                        'UserId' => 3,
                        'Name' => 'Julie',
                        'Email' => 'jim@email.com',
                    ],
                    [
                        'UserId' => 4,
                        'Name' => 'Jim',
                        'Email' => 'jim@email.com',
                    ],
                    [
                        'UserId' => 1,
                        'Name' => 'John',
                        'Email' => 'john@email.com',
                    ],
                    [
                        'UserId' => 2,
                        'Name' => 'Jane',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #5 Mutiple Records Order LIMIT 1
                'multiple',
                [ 'Email' => 'john@email.com' ],
                ['Name'], 1, null,
                [
                    [
                        'UserId' => 2,
                        'Name' => 'Jane',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #6 Mutiple Records Order LIMIT 1 OFFSET 1
                'multiple',
                [ 'Email' => 'john@email.com' ],
                ['Name'], 1, 1,
                [
                    [
                        'UserId' => 1,
                        'Name' => 'John',
                        'Email' => 'john@email.com',
                    ],
                ],
                Database::DEBUG_MODE
            ],
            [ // #7 Single record mutiple where clause
                'single',
                [ 'Email' => 'john@email.com', 'Name' => 'John' ],
                null, null, null,
                [
                    'UserId' => 1,
                    'Name' => 'John',
                    'Email' => 'john@email.com',
                ],
                Database::DEBUG_MODE
            ],
            [ // #8 Single record not found
                'single',
                [ 'Name' => 'Unknown' ],
                null, null, null,
                null,
                Database::DEBUG_MODE
            ],
            [ // #9 Single record not found
                'multiple',
                [ 'Name' => 'Unknown' ],
                null, null, null,
                [],
                Database::DEBUG_MODE
            ],
        ];
    }
    private function tearDownGetRecords(): void
    {
        $db = Database::__getInstance();
        $db->exec('DROP TABLE IF EXISTS `User`');
    }

    /**
     * @covers \PHPCore\Database::updateRecords
     * @covers \PHPCore\Database::__getInstance
     * @covers \PHPCore\Database::exec
     * @covers \PHPCore\Database::query
     * @covers \PHPCore\Database::_execStatement
     * @covers \PHPCore\Database::_cleanName
     * @covers \PHPCore\Database::_buildWhere
     * @covers \PHPCore\Database::_buildSet
     * @covers \PHPCore\Database::getLastExecException
     * @covers \PHPCore\Database::getLastInsertId
     * @covers \PHPCore\Database::getRowsAffected
     * @depends testGetInstance
     * @dataProvider dataProviderUpdateRecords
     */
    public function testUpdateRecords(array $updates, array $where, int $updateCount, int $flags = 0): void
    {
        $db = Database::__getInstance();
        $this->setUpUpdateRecords(false);
        $res = $db->query("SELECT * FROM `User`");
        if (count($res) !== 3) {
            $this->markTestSkipped('setUpUpdateRecord() did not setup database correctly');
            return;
        }

        $result = $db->updateRecords('User', $updates, $where, $flags);

        $debug_output = '';
        if ($flags & Database::DEBUG_MODE) {
            $type_val = gettype($result);
            //$type_val = gettype($result) . "($result)";
            $debug_output = "$type_val was returned.\n" . print_r($db->DebugData, true);
        }

        $debug_output = "Records removed to not matach expected count $debug_output";
        $this->assertEquals($result, $updateCount, $debug_output);

    }
    private function setUpUpdateRecords(bool $createTable = true): void
    {
        $db = Database::__getInstance();
        if ($createTable) {
            $db->exec('DROP TABLE IF EXISTS `User`');
            $db->exec('
                CREATE TABLE IF NOT EXISTS `User` (
                  `UserId` INTEGER PRIMARY KEY,
                  `Name` TEXT NULL,
                  `Email` TEXT NULL,
                  `Phone` TEXT NULL,
                  UNIQUE(`Name`)
                )
            ');
        } else {
            $db->exec("DELETE FROM `User`");
        }
        $db->exec("INSERT INTO `User` (`Name`, `email`, `Phone`) VALUES ('John', 'john@email.com', '')");
        $db->exec("INSERT INTO `User` (`Name`, `email`, `Phone`) VALUES ('Jane', 'john@email.com', '')");
        $db->exec("INSERT INTO `User` (`Name`, `email`, `Phone`) VALUES ('Tim', 'tim@email.com', '')");
    }
    private function dataProviderUpdateRecords(): array
    {
        return [
            [
                [ 'Phone' => '867-5309' ],
                [ 'UserId' => 1 ],
                1,
                Database::DEBUG_MODE
            ],
            [
                [ 'Phone' => '867-5309' ],
                [ 'Email' => 'john@email.com' ],
                2,
                Database::DEBUG_MODE
            ],
            [
                [ 'Phone' => '867-5309', 'Email' => 'updated@email.com' ],
                [ 'Email' => 'john@email.com', 'Name' => 'John' ],
                1,
                Database::DEBUG_MODE
            ],
        ];
    }
    private function tearDownUpdateRecords(): void
    {
        $db = Database::__getInstance();
        $db->exec('DROP TABLE IF EXISTS `User`');
    }

    /**
     * @covers \PHPCore\Database::deleteRecords
     * @covers \PHPCore\Database::__getInstance
     * @covers \PHPCore\Database::exec
     * @covers \PHPCore\Database::query
     * @covers \PHPCore\Database::_execStatement
     * @covers \PHPCore\Database::_cleanName
     * @covers \PHPCore\Database::_buildWhere
     * @covers \PHPCore\Database::getLastExecException
     * @covers \PHPCore\Database::getLastInsertId
     * @covers \PHPCore\Database::getRowsAffected
     * @depends testGetInstance
     * @dataProvider dataProviderDeleteRecords
     */
    public function testDeleteRecords(array $where, int $deleteCount, int $flags = 0): void
    {
        $db = Database::__getInstance();
        $this->setUpDeleteRecords(false);
        $res = $db->query("SELECT * FROM `User`");
        if (count($res) !== 3) {
            $this->markTestSkipped('setUpDeleteRecords() did not setup database correctly');
            return;
        }

        $result = $db->deleteRecords('User', $where, $flags);

        $debug_output = '';
        if ($flags & Database::DEBUG_MODE) {
            $type_val = gettype($result);
            //$type_val = gettype($result) . "($result)";
            $debug_output = "$type_val was returned.\n" . print_r($db->DebugData, true);
        }

        $debug_output = "Records removed to not matach expected count $debug_output";
        $this->assertEquals($result, $deleteCount, $debug_output);

    }
    private function setUpDeleteRecords(bool $createTable = true): void
    {
        $db = Database::__getInstance();
        if ($createTable) {
            $db->exec('DROP TABLE IF EXISTS `User`');
            $db->exec('
                CREATE TABLE IF NOT EXISTS `User` (
                  `UserId` INTEGER PRIMARY KEY,
                  `Name` TEXT NULL,
                  `Email` TEXT NULL,
                  UNIQUE(`Name`)
                )
            ');
        } else {
            $db->exec("DELETE FROM `User`");
        }
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('John', 'john@email.com')");
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('Jane', 'john@email.com')");
        $db->exec("INSERT INTO `User` (`Name`, `email`) VALUES ('Tim', 'tim@email.com')");
    }
    private function dataProviderDeleteRecords(): array
    {
        return [
            [ [ 'UserId' => 1 ], 1, Database::DEBUG_MODE ],
            [ [ 'Email' => 'john@email.com' ], 2, Database::DEBUG_MODE ],
        ];
    }
    private function tearDownDeleteRecords(): void
    {
        $db = Database::__getInstance();
        $db->exec('DROP TABLE IF EXISTS `User`');
    }

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////