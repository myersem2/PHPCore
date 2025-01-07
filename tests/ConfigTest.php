<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Config
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-03
 */

// -----------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;
use PHPCore\Config;
use PHPCore\ConfigException;

/**
 * Config Test Fixture
 *
 * This test fixture is uses to the Config class.
 */
final class ConfigTest extends TestCase
{
    /**
     * @prop string $getcwd Current working directory
     */
    private static string $getcwd;

    // ---------------------------------------------------------------------

    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        Config::clear();
        self::$getcwd = getcwd();
        chdir(__DIR__ . "{$ds}data");
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        Config::clear();
        Config::initialize();
        chdir(self::$getcwd);
        unset($_SERVER['PHPCORERC']);
    }

    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {
        // Place holder
    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {
        putenv('PHPCORERC');
        Config::clear();
    }

    // ---------------------------------------------------------------------

    /**
     * @covers \PHPCore\Config
     *
     * @testWith
     * [1]
     * [2]
     */
    public function testInitialize(int $count): void
    {
        //$this->markTestSkipped('Need to look into.');

        Config::initialize();
        if ($count === 1) {
            $this->assertTrue(true);
        } else {
            $this->expectException(ConfigException::class);
            $this->expectExceptionMessage('PHPCore config already initialized.');
            Config::initialize();
        }
    }

    /**
     * @covers \PHPCore\Config
     *
     * @testWith
     * ["", false]
     * ["phpcore_bad", true]
     * ["phpcore", false]
     */
    public function testEnvironmentConfig(string $file, bool $exception): void
    {
        //$this->markTestSkipped('Need to look into.');

        $ds = DIRECTORY_SEPARATOR;
        if (empty($file)) {
            putenv('PHPCORERC');
        } else {
            putenv('PHPCORERC=' . __DIR__ . "{$ds}data{$ds}$file.ini");
        }
        if ($exception) {
            $this->expectException(ConfigException::class);
            $this->expectExceptionMessage('PHPCore env config could not be loaded.');
        }
        Config::initialize();
        if ( ! $exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * @covers \PHPCore\Config
     *
     * @testWith
     * [{"version_lock":"0"},{"method":"assertNotEquals","value":"0"}]
     * [{"response.powered_by":"TEST:testRuntimeConfig"},{"method":"assertEquals","value":"TEST:testRuntimeConfig"}]
     * [{"unknown":"TEST:testRuntimeConfig"},{"method":"expectException"}]
     */
    public function testRuntimeConfig(array $config, array $result): void
    {
        //$this->markTestSkipped('Need to look into.');

        $option = array_keys($config)[0];
        $method = $result['method'];

        if ($method === 'expectException') {
            $this->expectException(ConfigException::class);
            $this->expectExceptionMessageMatches(
                '/Option `\w+` not set in phpcore.ini./'
            );
        }

        Config::initialize($config);

        if ($method !== 'expectException') {
            $this->$method(
                $result['value'],
                Config::get($option)
            );
        }
    }

    /**
     * @covers \PHPCore\Config
     * @covers ::phpcore_ini_restore
     *
     * @testWith
     * ["response.powered_by", "TEST:123", true, false]
     * ["response.powered_by", null, false, false]
     * ["response.powered_by", {"key":"value"}, true, true]
     * ["version_lock", "999", false, false]
     * ["unknown_option", "TEST", null, false]
     */
    public function testSetGetRestore(
        string $option,
        mixed $value,
        ?bool $changed,
        ?bool $exception
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        Config::initialize();
        $original = Config::get($option);

        if ($exception) {
            $this->expectException(ConfigException::class);
            $this->expectExceptionMessageMatches(
                '/Option `\w+.?(\w+)?` declared as string, array given./'
            );
        }

        Config::set($option, $value);

        if ($exception) {
            return;
        }

        if ($changed === null) {
            $this->assertNull(
                Config::get($option)
            );
            return;
        }
        
        $this->assertEquals(
            $changed ? $value : $original,
            Config::get($option)
        );

        Config::restore($option);

        $this->assertEquals(
            $original,
            Config::get($option)
        );
    }

    /**
     * @covers \PHPCore\Config
     *
     * @testWith
     * ["response", {"response.powered_by":"TEST:abc"}, false, {"response.powered_by":"TEST:abc"}]
     * ["response", {"response.powered_by":"TEST:abc"}, true, {"response.powered_by":{"local":"TEST:abc","master":"PHPCore"}}]
     * ["unknown", {"test.two":"3"}, false, null]
     * ["unknown", {"test.two":"3"}, true, null]
     */
    public function testGetAll(
        string $section,
        array $change,
        bool $details,
        ?array $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        Config::initialize();

        $option = array_keys($change)[0];
        $value = array_values($change)[0];
        Config::set($option, $value);

        if ($expected === null) {
            $this->assertNull(
                Config::getAll($option)
            );
            return;
        } else {
            $this->assertEquals(
                $expected,
                Config::getAll($section, $details)
            );
        }
    }

    /**
     * @covers \PHPCore\Config
     *
     * @testWith
     * ["Badconfigarraytype", {"badconfigarraytype.string":"test"}, "/Option `\\w+.?(\\w+)?` type not set in phpcore.ini./"]
     * ["Badconfigunkowntype", {"badconfigunkowntype.string":"test"}, "/Config option `\\w+.?(\\w+)?` unknown type unknown_type./"]
     */
    public function testBadClassConfig(
        string $file,
        array $config,
        string $pattern
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $ds = DIRECTORY_SEPARATOR;
        
        include_once __DIR__ . "{$ds}data{$ds}$file.php";

        $option = array_keys($config)[0];

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessageMatches($pattern);

        Config::initialize($config);
    }

    /**
     * @covers \PHPCore\Config
     * @covers ::phpcore_ini_get
     * @covers ::phpcore_ini_set
     * @covers ::phpcore_ini_restore
     * @covers ::phpcore_ini_get_all
     *
     * @testWith
     * ["response.powered_by", "TEST:123", true]
     * ["response.powered_by", null, false]
     * ["version_lock", "999", false]
     * ["unknown_option", "TEST", null]
     */
    public function testAliasFunctions(
        string $option,
        ?string $value,
        ?bool $changed
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        Config::initialize();
        $original = phpcore_ini_get($option);

        phpcore_ini_set($option, $value);

        if ($changed === null) {
            $this->assertNull(
                phpcore_ini_get($option)
            );
            return;
        } else {
            $this->assertEquals(
                $changed ? $value : $original,
                phpcore_ini_get($option)
            );
        }

        phpcore_ini_restore($option);

        $this->assertEquals(
            $original,
            phpcore_ini_get($option)
        );

        if ($option === 'response.powered_by') {
            $options = phpcore_ini_get_all('response', false);
            $this->assertEquals(
                $original,
                $options['response.powered_by']
            );
        }
    }
}

// EOF /////////////////////////////////////////////////////////////////////////