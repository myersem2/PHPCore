<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Request
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;
use PHPCore\Request;

/**
 * @backupGlobals enabled
 * @backupStaticAttributes enabled
 */
final class RequestTest extends TestCase
{
    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        // Base for testing
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.0.1';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['REQUEST_URI'] = '/sections/articles/index.php?view=default&offset=1000';
        $_SERVER['REQUEST_TIME_FLOAT'] = 1681363597.2922;
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {

    }

    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {

    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {

    }

    // -----------------------------------------------------------------------------------------

    /**
     * @covers \PHPCore\Request
     * @covers ::request_agent
     *
     * @testWith ["version", "109.0"]
     *           [null, null]
     *           ["isfake", false]
     *           ["majorver", 109]
     */
    public function testRequestAgent(?string $key, mixed $expected): void
    {
        $actual = request_agent($key);

        if ( ! isset($key)) {
            // Because the object could change from native get_browser()
            $this->assertIsObject($actual);
        } else {
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::request_ip
     * @covers ::core_ini_get
     * @covers ::core_ini_set
     *
     * @testWith ["REMOTE_ADDR", "10.0.0.1"]
     *           ["HTTP_X_FORWARDED_FOR", "192.168.0.1"]
     *           ["HTTP_X_UNKNOWN", false]
     * !runInSeparateProcess
     */
    public function testRequestIp(string $ip_var, string|bool $expected): void
    {
        core_ini_set('request.ip_var', $ip_var);

        $actual = request_ip();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::request_id
     * @covers ::core_ini_get
     * @covers ::core_ini_set
     *
     * @testWith ["REMOTE_ADDR", "13280aed3cfbd4594f699ceac4414885"]
     *           ["HTTP_X_UNKNOWN", "a84dae42e4c3bb9970498daee1172d9f"]
     * !runInSeparateProcess
     */
    public function testRequestId(string $ip_var, string $result): void
    {
        core_ini_set('request.ip_var', $ip_var);

        $actual = request_id();

        $this->assertSame($result, $actual);
    }

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////