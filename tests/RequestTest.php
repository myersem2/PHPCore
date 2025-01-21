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
use PHPCore\Exceptions\RequestException;
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
        // place holder
    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {
        // place holder
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

        if ( ! empty($agent)) {
            $this->assertEquals(
                $agent,
                (array)Request::getAgent()
            );
        }

        // tearDown()
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     *
     * @runInSeparateProcess
     *
     * @testWith
     * [{"num":123, "text":"abc"}, "text", "", "", "abc"]
     * [{"num":123, "text":"abc"}, "text", "FILTER_VALIDATE_INT", "", false]
     * [{"num":123, "text":"abc"}, "num", "FILTER_VALIDATE_INT", "", 123]
     * [{"num":123, "text":"abc"}, "unk", "", "", null]
     * [{"num":123, "text":"abc"}, null, "", "", {"num":123, "text":"abc"}]
     */
    public function testBodyPost(
        array $post,
        ?string $key,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_POST = $post;

        if ( ! empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if ( ! empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getBody($key),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getBody($key, $filter, $options)
            );
        }

        // tearDown()
        $_POST = [];
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     * @covers ::csv_parse_file
     *
     * @runInSeparateProcess
     *
     * @testWith
     * ["test.xml", "text/xml", "name", "", "", "Test"]
     * ["test.json", "text/json", "name", "", "", "Test"]
     * ["test.csv", "text/csv", "0", "", "", {"name":"Test","value":"123"}]
     * ["test.csv", "text/csv", "1", "", "", {"name":"John","value":"456"}]
     * ["test.yaml", "text/yaml", "name", "", "", "Test"]
     */
    public function testBodyInput(
        string $file,
        string $content_type,
        ?string $key,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_SERVER['CONTENT_TYPE'] = $content_type;

        Config::set(
            'request.input_stream',
            __DIR__ . "/data/$file"
        );

        if ( ! empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if ( ! empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getBody($key),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getBody($key, $filter, $options)
            );
        }

        unset($_SERVER['CONTENT_TYPE']);
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith
     * [{"num":123, "text":"abc"}, "text", "", "", "abc"]
     * [{"num":123, "text":"abc"}, "text", "FILTER_VALIDATE_INT", "", false]
     * [{"num":123, "text":"abc"}, "num", "FILTER_VALIDATE_INT", "", 123]
     * [{"num":123, "text":"abc"}, "unk", "", "", null]
     * [{"num":123, "text":"abc"}, null, "", "", {"num":123, "text":"abc"}]
     */
    public function testCookie(
        array $cookies,
        ?string $key,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_COOKIE = $cookies;

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getCookie($key),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getCookie($key, $filter, $options)
            );
        }

        // tearDown()
        $_COOKIE = [];
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

        // tearDown()
        unset($_SERVER['CONTENT_TYPE']);
        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * @covers \PHPCore\Request
     * @covers ::array_find
     *
     * @testWith
     * [{"HTTP_PAGINATION_OFFSET":"1", "HTTP_PAGINATION_ORDER":"asc"}, "PAGINATION_OFFSET", "", "", "1"]
     * [{"HTTP_PAGINATION_OFFSET":"1", "HTTP_PAGINATION_ORDER":"asc"}, "PAGINATION_OFFSET", "FILTER_VALIDATE_INT", "", 1]
     * [{"HTTP_PAGINATION_OFFSET":"1", "HTTP_X_PAGINATION_OFFSET":"2"}, "PAGINATION_OFFSET", "FILTER_VALIDATE_INT", "", 1]
     * [{"PAGINATION_OFFSET":"1"}, "PAGINATION_OFFSET", "", "", null]
     * [{"HTTP_PAGINATION_OFFSET":"1"}, null, "", "", {"PAGINATION_OFFSET":"1"}]
     */
    public function testHttpHeader(
        array $server_params,
        ?string $key,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        foreach ($server_params as $k => $v) {
            $_SERVER[$k] = $v;
        }

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getHttpHeader($key),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getHttpHeader($key, $filter, $options)
            );
        }

        foreach ($server_params as $k => $v) {
            unset($_SERVER[$k]);
        }
    }

    /**
     * @covers \PHPCore\Request
     *
     * @testWith
     * [{"text":"abc","num":"123"}, "text", "", "", "abc"]
     * [{"text":"abc","num":"123"}, "num", "FILTER_VALIDATE_INT", "", 123]
     * [{"text":"abc","num":"123"}, "text", "FILTER_VALIDATE_INT", "", false]
     * [{"text":"abc","num":"123"}, "unk", "", "", null]
     * [{"text":"abc","num":"123"}, null, "", "", {"text":"abc","num":"123"}]
     */
    public function testParameter(
        array $params,
        ?string $key,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        foreach ($params as $k => $v) {
            $_GET[$k] = $v;
        }

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getParameter($key),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getParameter($key, $filter, $options)
            );
        }

        // tearDown()
        $_GET = [];
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     *
     * @testWith
     * ["/a/b/c.php?q=123", 0, 1, "", "", "b"]
     * ["/a/b/c.php?q=123", 1, 1, "", "", "c"]
     * ["/a/b/c.php?q=123", 0, null, "", "", ["a","b","c"]]
     * ["/a/b/c.php?q=123", 1, -1, "", "", null]
     * ["/a/123.php?q=123", 0, 0, "FILTER_VALIDATE_INT", "", false]
     * ["/a/123.php?q=123", 0, 1, "FILTER_VALIDATE_INT", "", 123]
     */
    public function testSegment(
        string $uri,
        int $offset,
        ?int $pos,
        string $filter,
        string $options,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        $_SERVER['REQUEST_URI'] = $uri;
        Config::set('request.segment_offset', $offset);

        if (!empty($filter)) {
            $filter = constant($filter);
        } else {
            $filter = null;
        }

        if (!empty($options)) {
            $options = constant($options);
        } else {
            $options = 0;
        }

        if (is_null($filter)) {
            $this->assertEquals(
                $expected,
                Request::getSegment($pos),
            );
        } else {
            $this->assertEquals(
                $expected,
                Request::getSegment($pos, $filter, $options),
            );
        }

        // tearDown()
        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\Config
     * @covers ::array_find
     *
     * @runInSeparateProcess
     *
     * @testWith
     * [{"HTTP_X_FORWARDED_FOR":"10.0.0.2","REMOTE_ADDR":"10.0.0.3"}, ["HTTP_X_FORWARDED_FOR","REMOTE_ADDR"], "10.0.0.2"]
     * [{"HTTP_X_FORWARDED_FOR":"10.0.0.2","REMOTE_ADDR":"10.0.0.3"}, ["REMOTE_ADDR"], "10.0.0.3"]
     * [{"HTTP_X_FORWARDED_FOR":"10.0.0.2","REMOTE_ADDR":"10.0.0.3"}, ["UNKNOWN"], null]
     * [{"REMOTE_ADDR":"10.0.3"}, ["REMOTE_ADDR"], false]
     * [[], [], null]
     */
    public function testIpAddress(
        array $server_params,
        array $ip_server_params,
        mixed $expected
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        foreach ($server_params as $k => $v) {
            $_SERVER[$k] = $v;
        }

        Config::set(
            'request.ip_server_params',
            $ip_server_params
        );

        if (empty($server_params)) {
            $code = RequestException::CONFIG_ERR_IP_SVR_PARM;
            $this->expectException(RequestException::class);
            $this->expectExceptionCode($code);
        }

        $this->assertEquals(
            $expected,
            Request::getIpAddress()
        );

        foreach ($server_params as $k => $v) {
            unset($_SERVER[$k]);
        }
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\RequestFile
     * @covers \PHPCore\Config
     *
     * @note You MUST use a different **$field** for each or the `static $files`
     *       in the Request::getFile() will return the incorrect data
     *
     * @testWith
     * ["test.csv","text/csv",0, 0, {"error":0}]
     * ["test.csv","text/csv",3, 0, {"error":3,"contents":null}]
     * ["test.csv","text/csv",0, 2, {"error":9,"error_message":"File was not uploaded via HTTP POST"}]
     * ["test.json","text/json",0, 0, {"error":0,"contents":"{\"name\":\"Test\",\"value\":123}"}]
     * ["test.csv","text/json",0, 4, {"error":10}]
     * ["test.csv","text/json",0, 5, {"exception":true,"error":10}]
     * ["test.csv","text/csv",0, 0, {"file_null":true}]
     */
    public function testFile(
        string $name,
        string $type,
        int $error,
        int $flags,
        array $result
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        static $run = 0;
        $run++;

        // TODO: Look into this https://dev.to/icolomina/testing-an-external-api-using-phpunit-m8j

        $_FILES = [
            "f$run" => [
                'name'      => $name,
                'full_path' => $name, // NOTE: New PHP 8.1 feature https://php.watch/versions/8.1/$_FILES-full-path
                'type'      => $type,
                'tmp_name'  => __DIR__ . "/data/$name",
                'error'     => $error,
                'size'      => 41,
            ],
        ];

        if ( ! empty($result['exception'])) {
            $this->expectException(RequestException::class);
            $this->expectExceptionCode($result['error']);
        }

        $key = empty($result['file_null']) ? "f$run" : 'unknown';
        $fileObj = Request::getFile($key, $flags);

        if ( ! empty($result['file_null'])) {
            $this->assertNull($fileObj);
            return;
        }

        $this->assertEquals(
            $result['error'],
            $fileObj->error
        );

        if (array_key_exists('contents', $result)) {
            if ($result['contents'] === null) {
                $this->assertNull($fileObj->getContents());
            } else {
                $this->assertEquals(
                    $result['contents'],
                    $fileObj->getContents()
                );
            }
        }

        if ( ! empty($result['error_message'])) {
            $this->assertEquals(
                $result['error_message'],
                $fileObj->getErrorMessage()
            );
        }

        // tearDown()
        $_FILES = [];
    }

    /**
     * @covers \PHPCore\Request
     * @covers \PHPCore\RequestFile
     * @covers \PHPCore\Config
     *
     * @note You MUST use a different **$field** for each or the `static $files`
     *       in the Request::getFile() will return the incorrect data
     *
     * @testWith
     * [{"name":"test.csv","type":"text/csv"},{"name":"test.json","type":"text/json"},{"count":2}]
     * [{"name":"test.csv","type":"text/csv"},{"name":"test.json","type":"text/json"},{"file_null":true}]
     */
    public function testFiles(
        array $file_a,
        array $file_b,
        array $result
    ): void
    {
        //$this->markTestSkipped('Need to look into.');

        static $run = 0;
        $run++;

        // TODO: Look into this https://dev.to/icolomina/testing-an-external-api-using-phpunit-m8j

        $_FILES = [
            "f$run" => [
                'name'      => [$file_a['name'],$file_b['name']],
                'full_path' => [$file_a['name'],$file_b['name']],
                'type'      => [$file_a['type'],$file_b['type']],
                'tmp_name'  => [__DIR__ . "/data/{$file_a['name']}",__DIR__ . "/data/{$file_a['name']}"],
                'error'     => [0,0],
                'size'      => [41,41],
            ],
        ];

        $key = empty($result['file_null']) ? "f$run" : 'unknown';
        $files = Request::getFiles($key);

        if ( ! empty($result['file_null'])) {
            $this->assertNull($files);
            return;
        }

        if (array_key_exists('count', $result)) {
            $this->assertEquals(
                $result['count'],
                count($files)
            );
        }

        if (array_key_exists('count', $result)) {
            $this->assertEquals(
                $result['count'],
                count($files)
            );
        }

        // tearDown()
        $_FILES = [];
    }
}

// EOF /////////////////////////////////////////////////////////////////////////