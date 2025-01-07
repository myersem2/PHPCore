<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Session
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
use PHPCore\Session;

/**
 * Session Test Fixture
 *
 * This test fixture is uses to the Session class.
 */
final class SessionTest extends TestCase
{
    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        // Place holder
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        $_COOKIE = [];
    }

    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {
        $_COOKIE = [];
        //list($setUp) = explode(' ', 'setUp' . substr($this->getName(), 4));
        //if (method_exists($this, $setUp) === true) {
        //    $this->$setUp();
        //}
    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {
        //list($tearDown) = explode(' ', 'tearDown' . substr($this->getName(), 4));
        //if (method_exists($this, $tearDown) === true) {
        //    $this->$tearDown();
        //}
    }

    // ---------------------------------------------------------------------

    /**
     * @covers \PHPCore\Session
     * @runInSeparateProcess
     */
    public function testSessionClassExists(): void
    {
        // todo look at setting this up correctly for session_start
    }

    /**
     * @coversNothing
     * @depends testSessionClassExists
     * @dataProvider dataProviderMethodsExist
     *
    public function testMethodsExist(string $method): void
    {
        $this->assertTrue(
            method_exists('\PHPCore\Session', $method),
            "Method `$method` was not found in the Session class."
        );
    }
    public function dataProviderMethodsExist(): array
    {
        return [
            ['getInstance'],
            ['__construct'],
        ];
    }

    /**
     * @covers \PHPCore\Session::__construct
     *
    public function testGetInstance(): void
    {
        $this->assertInstanceOf(
            Session::class,
            Session::getInstance()
        );
        
        //Session::getInstance()->flushAll();
        //print_r(Session::getInstance()->getAllKeys());
        
    }
    /* */
}

// EOF /////////////////////////////////////////////////////////////////////////