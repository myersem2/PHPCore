<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - RequestHttp
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;
use PHPCore\Request;

/**
 * @backupGlobals enabled
 */
final class RequestHttpTest extends TestCase
{
    /**
     * Request Instance
     *
     * This Request object is only constructed once in setUpBeforeClass()
     */
    private static ?object $RequestInstance;

    // ---------------------------------------------------------------------

    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {
        // Base PHP overrite for this test fixture
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html/';
        $_SERVER['PHP_SELF'] = '/test.php';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/html/test.php';
        $_SERVER['SCRIPT_NAME'] = '/test.php';

        // Base Apache
        $_SERVER['CONTEXT_DOCUMENT_ROOT'] = '/var/www/html/';
        $_SERVER['CONTEXT_PREFIX'] = '';
        $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.2';
        $_SERVER['REMOTE_PORT'] = '59494';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['REQUEST_URI'] = '/test.php?limit=10';
        $_SERVER['SERVER_ADDR'] = '10.0.0.1';
        $_SERVER['SERVER_ADMIN'] = 'webmaster@domain.com';
        $_SERVER['SERVER_NAME'] = 'domain.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_SIGNATURE'] = 'Apache/2.4.52 (Ubuntu) Server at domain.com Port 80';
        $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.4.52 (Ubuntu)';

        // HTTP Heders
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7';
        /** NOTE: this comment block is needed due to function list generator breaking on  /* above ***/
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
        $_SERVER['HTTP_CONNECTION'] = 'keep-alive';
        $_SERVER['HTTP_COOKIE'] = 'SessionID=TrGrgnCUeYaE0t5NM6yXrqN1NlbVEWJa';
        $_SERVER['HTTP_HOST'] = 'domain.com';
        $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] = '1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.3';
        
        // Custom for testing only
        $_SERVER['REMOTE_ADDR_IPV6'] = 'fc00:0000:0000:0000:0000:0000:0000:0001';
        $_SERVER['BAD_REMOTE_ADDR'] = 'NOT-A-VALID-IP';

        self::$RequestInstance = new Request([
            'php_sapi_name' => 'apache2handler', // NOTE: simulates HTTP request
        ]);
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        self::$RequestInstance = null;
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
     *
     * @testWith ["version", "109.0"]
     *           [null, null]
     *           ["isfake", false]
     *           ["majorver", 109]
     */
    public function testRequestAgent(?string $key, mixed $expected): void
    {
        // Arrange & Act
        $actual = self::$RequestInstance->agent($key);

        // Assert
        if ( ! isset($key)) {
            $this->assertIsObject($actual);
        } else {
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestBody(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestCookie(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestFile(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestFiles(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestFormat(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestHeader(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::core_ini_get
     * @covers ::core_ini_set
     *
     * @testWith ["REMOTE_ADDR", "10.0.0.2", false]
     *           ["HTTP_X_FORWARDED_FOR", "10.0.0.3", false]
     *           ["REMOTE_ADDR_IPV6", "fc00:0000:0000:0000:0000:0000:0000:0001", false]
     *           ["BAD_REMOTE_ADDR", false, false]
     *           ["NONEXISTENT", false, false]
     *           ["NONEXISTENT", "10.0.0.4", true]
     */
    public function testRequestIp(string $ip_param, bool|string $expected, bool $pass_header): void
    {
        // Arrange
        core_ini_set('http_request.ip_param', $ip_param);
        if ($ip_param != 'REMOTE_ADDR') {
            $new_instance = new Request([
                'php_sapi_name' => 'apache2handler', // NOTE: simulates HTTP request
                'ip_address'    => ($pass_header) ? $expected : false,
            ]);
        }

        // Act
        if ($ip_param == 'REMOTE_ADDR') {
            $actual = self::$RequestInstance->ip();
        } else {
            $actual = $new_instance->ip();
        }

        // Assert
        if ($expected === false) {
            $this->assertFalse($actual);
        } else {
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::core_ini_get
     * @covers ::core_ini_set
     *
     * @testWith [true]
     *           [false]
     */
    public function testRequestId(bool $new_request): void
    {
        // Arrange
        $pattern = '/^[a-f0-9]{32}$/';
        if ($new_request) {
            $new_instance = new Request([
                'php_sapi_name' => 'apache2handler', // NOTE: simulates HTTP request
                'ip_address'    => '10.0.0.4',
            ]);
        }

        // Act
        if ($new_request) {
            $actual = $new_instance->id();
        } else {
            $actual = self::$RequestInstance->id();
        }

        // Assert
        $this->assertMatchesRegularExpression($pattern, $actual);
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestParam(): void
    {
        $this->markTestSkipped('Not built');
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith []
     */
    public function testRequestSegment(): void
    {
        $this->markTestSkipped('Not built');
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////