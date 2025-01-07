<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Request
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
use PHPCore\Request;
use PHPCore\RequestException;
use PHPCore\Config;

/**
 * @backupGlobals enabled
 */
final class RequestTest extends TestCase
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
        /*
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
        */

        // HTTP Heders
        //$_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7';
        /** NOTE: this comment block is needed due to function list generator breaking on  /* above ***/
        /*
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
        $_SERVER['HTTP_CONNECTION'] = 'keep-alive';
        $_SERVER['HTTP_COOKIE'] = 'SessionID=TrGrgnCUeYaE0t5NM6yXrqN1NlbVEWJa';
        $_SERVER['HTTP_HOST'] = 'domain.com';
        $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] = '1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.3';
        
        // Custom for testing only
        $_SERVER['REMOTE_ADDR_IPV6'] = 'fc00:0000:0000:0000:0000:0000:0000:0001';
        $_SERVER['BAD_REMOTE_ADDR'] = 'NOT-A-VALID-IP';

        self::$RequestInstance = new Request([
            'php_sapi_name' => 'apache2handler', // NOTE: simulates HTTP request
        ]);
        */
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        //self::$RequestInstance = null;
    }

    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    }

    // ---------------------------------------------------------------------

    /**
     * @covers \PHPCore\Request
     *
     * @runInSeparateProcess
     *
     * @testWith
     * ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36", {"platform":"Win10","ismobiledevice":false}, {}]
     * ["Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0", {"platform":"MacOSX","version":"42.0"}, {}]
     * ["Unknown Agent", {"platform":"unknown","istablet":false}, {}]
     * ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36", {}, {"browser_name_regex":"~^mozilla\/5\\.0 \\(.*windows nt 10\\.0.*\\) applewebkit.* \\(.*khtml.*like.*gecko.*\\) chrome\/131\\.0.*safari\/.*$~","browser_name_pattern":"Mozilla\/5.0 (*Windows NT 10.0*) applewebkit* (*khtml*like*gecko*) Chrome\/131.0*Safari\/*","parent":"Chrome 131.0","platform":"Win10","comment":"Chrome 131.0","browser":"Chrome","version":"131.0","device_type":"Desktop","ismobiledevice":false,"istablet":false}]
     */
    public function testAgent(
        string $http_user_agent,
        array $checks,
        array $agent
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_SERVER['HTTP_USER_AGENT'] = $http_user_agent;

        foreach ($checks as $key => $expected) {
            if (empty(ini_get('browscap'))) {
                $expected = null;
            }
            $this->assertEquals(
                $expected,
                Request::getAgent($key)
            );
        }

        if (!empty($agent)) {
            $this->assertEquals(
                $agent,
                (array)Request::getAgent()
            );
        }
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith
     * [{"PaginationOffset":1, "PaginationOrder":"asc"}, {"PaginationOffset":1, "PaginationOrder":"asc"}]
     * [{"PaginationOffset":"1"}, {"PaginationOffset":1}, "FILTER_VALIDATE_INT"]
     * [{"PaginationOffset":"asc"}, {"PaginationOrder":false}, "FILTER_VALIDATE_INT"]
     * [{"Referer":"https://google.com"}, {"Referer":"https://google.com"}, "FILTER_VALIDATE_URL"]
     * [{"Referer":"https://google.com"}, {"Referer":false}, "FILTER_VALIDATE_URL", "FILTER_FLAG_QUERY_REQUIRED"]
     * [{}, {"NonExistant":null}]
     */
    public function testCookie(
        array $cookies,
        array $checks,
        string $filter = "",
        array|string $options = ""
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_COOKIE = $cookies;

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options) && !is_array($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        foreach ($checks as $key => $expected) {
            $this->assertEquals(
                $expected,
                Request::getCookie($key, $filter, $options)
            );
        }
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     *
     * @testWith
     * ["application/x-www-form-urlencoded", null, "xml"]
     * ["application/json", null, "json"]
     * ["application/x-yaml", null, "yaml"]
     * ["text/json", null, "json"]
     * ["text/yaml", null, "yaml"]
     * ["text/csv", null, "csv"]
     * ["text/unknown", null, null]
     * [null, "/", null]
     * [null, "/resource.xml", "xml"]
     * [null, "/resource.xml?query=test", "xml"]
     * [null, "/resource.unknown", null]
     * ["application/x-www-form-urlencoded", "/resource.yaml", "xml"]
     */
    public function testFormat(
        ?string $content_type,
        ?string $request_uri,
        ?string $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        Config::set('request.default_format', 'json');

        $_SERVER['CONTENT_TYPE'] = $content_type;
        $_SERVER['REQUEST_URI'] = $request_uri;

        $this->assertEquals(
            $expected ?? 'json',
            Request::getFormat()
        );
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::array_find
     *
     * @testWith
     * [{"HTTP_PAGINATION_OFFSET":1, "HTTP_PAGINATION_ORDER":"asc"}, {"PAGINATION_OFFSET":1, "PAGINATION_ORDER":"asc"}]
     * [{"HTTP_PAGINATION_OFFSET":"1"}, {"PAGINATION_OFFSET":1}, "FILTER_VALIDATE_INT"]
     * [{"HTTP_PAGINATION_OFFSET":"asc"}, {"PAGINATION_OFFSET":false}, "FILTER_VALIDATE_INT"]
     * [{"HTTP_PAGINATION_OFFSET":1, "HTTP_X_PAGINATION_OFFSET":2}, {"PAGINATION_OFFSET":1}]
     * [{"HTTP_PAGINATION_OFFSET":""}, {"NON_EXISTANT":null}]
     * [{}, {"NON_EXISTANT":null}]
     */
    public function testHeader(
        array $headers,
        array $checks,
        string $filter = "",
        array|string $options = ""
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        foreach ($headers as $key => $value) {
            $_SERVER[$key] = $value;
        }

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options) && !is_array($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        foreach ($checks as $key => $expected) {
            $this->assertEquals(
                $expected,
                Request::getHeader($key, $filter, $options),
                "Key $key failed"
            );
        }
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     * @covers ::array_find
     *
     * @runInSeparateProcess
     *
     * @testWith
     * [{"HTTP_X_FORWARDED_FOR":null,"REMOTE_ADDR":null}, null]
     * [{"HTTP_X_FORWARDED_FOR":null,"REMOTE_ADDR":"10.0.0.3"}, "10.0.0.3"]
     * [{"HTTP_X_FORWARDED_FOR":"10.0.0.2","REMOTE_ADDR":"10.0.0.3"}, "10.0.0.2"]
     * [{"SSH_CONNECTION":"10.0.0.1 48678 10.0.0.10 22","HTTP_X_FORWARDED_FOR":"10.0.0.2","REMOTE_ADDR":"10.0.0.3"}, "10.0.0.1"]
     * [{"REMOTE_ADDR":"fc00:0000:0000:0000:0000:0000:0000:0001"}, "fc00:0000:0000:0000:0000:0000:0000:0001"]
     * [{"NON_EXISTENT":"10.0.0.1"}, null, false]
     * [{}, null]
     */
    public function testIp(
        array $ip_server_params,
        ?string $expected,
        bool $server_set = true
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        if ($server_set) {
            foreach ($ip_server_params as $key => $value) {
                $_SERVER[$key] = $value;
            }
        }

        if (empty($ip_server_params) && $server_set) {
            $this->expectException(RequestException::class);
            $this->expectExceptionMessage(
                'Empty `request.ip_server_params` in phpcore.ini'
            );
        }

        Config::set(
            'request.ip_server_params',
            array_keys($ip_server_params)
        );
        $first_ip = Request::getIpAddress();
        $same_ip = Request::getIpAddress();

        $this->assertEquals(
            $expected,
            $first_ip
        );

        $this->assertEquals(
            $first_ip,
            $same_ip,
            "Second match"
        );
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
     * @covers ::core_ini_get
     * @covers ::core_ini_set
     *
     * @testWith [true]
     *           [false]
     */
    public function testRequestId(bool $new_request): void
    {
        $this->markTestSkipped('Not built');

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