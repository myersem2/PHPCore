<?php declare(strict_types=1);
/**
 * PHPCore - Request
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Request Class
 *
 * The Request class is used to simplify working with data send via the http protocal.
 *
 * @seealso `PHPCore Request Functions`_ - Simplified functions that interface directly with the
 *          `PHPCore Request Class`_.
 *
 * @refence PHPCore Request Class: ../classes/request.html
 * @refence PHPCore Request Functions: ../functions/request.html
 * @refence PHP Filter Variable: https://www.php.net/manual/en/function.filter-var.php
 * @refence PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
 */
#[Test('../tests/RequestHttpTest.php')]
#[Documentation('../docs/classes/request.rst')]
final class Request
{
    /**
     * Instance that have been created
     *
     * @prop array
     */
    protected static array $Instances = [];

    /**
     * Agent Booleans
     *
     * @prop array
     */
    private static array $AgentBooleans = [
        'activexcontrols','alpha','backgroundsounds','beta','cookies',
        'crawler','frames','javascript','iframes','isanonymized','isfake',
        'ismobiledevice','ismodified','issyndicationreader','istablet',
        'javaapplets','tables','vbscript','win16','win32','win64',
    ];

    /**
     * Agent Integers
     *
     * @prop array
     */
    private static array $AgentIntegers = [
        'aolversion','browser_bits','cssversion','majorver','minorver',
        'platform_bits',
    ];

    // ---------------------------------------------------------------------

    /**
     * Agent
     *
     * @prop ?object
     */
    public readonly ?object $Agent;

    /**
     * Cookies
     *
     * @prop array
     */
    public readonly array $Cookies;

    /**
     * Format
     *
     * @prop ?string
     */
    public readonly ?string $Format;

    /**
     * Headers
     *
     * @prop array
     */
    public readonly array $Headers;

    /**
     * IP Address
     *
     * @prop string
     */
    public readonly bool|string $IpAddress;

    /**
     * Request ID
     *
     * @prop ?string
     */
    public readonly ?string $RequestId;

    /**
     * Request Time Start
     *
     * @prop string
     */
    public readonly float $RequestTimeStart;

    // ---------------------------------------------------------------------

    /**
     * Get request object
     *
     * This method is used to retrive a previously constructed request instance
     * by a given `$request_id`.
     *
     * @param ?string $request_id Request ID
     * @return ?PHPCore\Request Request instance
     */
    public static function &getRequest(?string $request_id = null): ?Request
    {
        if (empty(self::$Instances)) {
            throw new Exception('PHPCore\Request::getRequest() cannot be invoked because no Request instances exist.');
        }

        if ( ! isset($request_id)) {
            if (count(self::$Instances) > 1) {
                throw new Exception('PHPCore\Request::getRequest() cannot be invoked without the request_id parameter with more that one Request instance.');
            }
            $request_id = array_keys(self::$Instances)[0];
        }

        if ( ! isset(self::$Instances[$request_id])) {
            throw new Exception("Request ID `$request_id` was not found.");
        }

        return self::$Instances[$request_id];
    }

    // ---------------------------------------------------------------------

    /**
     * Constructor
     *
     * Used to construct the instance and it by reference into the
     * self::$Instances for later use.
     *
     * @param array $params Parameters for request
     * @return void
     */
    public function __construct(array $params = [])
    {
        $php_sapi_name = $params['php_sapi_name'] ?? php_sapi_name();
        $agent = null;
        $cookies = $params['cookies'] ?? [];
        $format = $params['format'] ?? null;
        $headers = $params['headers'] ?? [];
        $ip_address = $params['ip_address'] ?? false;
        $request_id = $params['request_id'] ?? null;
        $request_time = $params['request_time'] ?? $_SERVER['REQUEST_TIME_FLOAT'];

        // CLI
        if ($php_sapi_name == 'cli') {
            
            if ( ! $ip_address && isset($_SERVER['SSH_CONNECTION'])) {
                list($ip_address) = explode(' ', $_SERVER['SSH_CONNECTION']);
            }

        // Standard HTTP request
        } else {

            if (empty($headers)) {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
            }

            if (empty($cookies)) {
                $cookies = $_COOKIE;
            }

            $ip_param = core_ini_get('http_request.ip_param');
            if ( ! $ip_address && isset($_SERVER[$ip_param])) {
                $ip_address = $_SERVER[$ip_param];
            }

            if ( ! isset($request_id)) {
                $time = strval($request_time);
                $request_id = md5("{$time}{$ip_address}");
            }

        }

        if ( ! filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $ip_address = false;
        }

        if (isset($headers['User-Agent'])) {
            $agent = get_browser($headers['User-Agent']) ?? null;
            if (isset($agent)) {
                foreach (self::$AgentBooleans as $prop) {
                    $agent->$prop = boolval($agent->$prop);
                }
                foreach (self::$AgentIntegers as $prop) {
                    $agent->$prop = intval($agent->$prop);
                }
            }
        }


        $content_type = $header['Content-Type'] ?? null;
        $format = match ($content_type) {
            'application/x-www-form-urlencoded' => 'xml',
            'text/json', 'application/json'     => 'json',
            'text/yaml', 'application/yaml'     => 'yaml',
            'text/csv'                          => 'csv',
            null    => null, // BUG: Need due to but see - https://github.com/php/php-src/issues/11134
            default => null,
        };
        if (empty($format) && isset($_SERVER['REQUEST_URI'])) {
            list($path) = explode('?', $_SERVER['REQUEST_URI'] ?? '');
            $dotLocation = strripos($path, '.');
            if ($dotLocation !== false) {
                $format = strtolower(substr($path, $dotLocation + 1));
            }
        }
        if (empty($format)) {
            $format = $GLOBALS['_CORE']['FORMAT'] ?? 'json';
        }

        $this->Agent = $agent;
        $this->Cookies = $cookies;
        $this->Format = $format;
        $this->Headers = $headers;
        $this->requestId = $request_id;
        $this->IpAddress = $ip_address;
        $this->RequestTimeStart = $request_time;

        if (isset(self::$Instances[$this->requestId])) {
            trigger_error('PHPCore\Request was contructed with the a Request ID that already exists.', E_USER_WARNING);
        }

        self::$Instances[$this->requestId] =& $this;
    }

    // ---------------------------------------------------------------------

    /**
     * Get request agent capabilities
     *
     * Attempts to determine the capabilities of the user's browser by looking up the browser's
     * information in the browscap.ini file. Then returns the capability by the given **$key**.
     *
     * If **$key** is not passed the entire capabilities object will be returned.
     *
     * @note Returns **NULL** if get_browser() fails or requested capability is unknown.
     *
     * @example Get request agent capabilities
     * <code linenos="true" emphasize-lines="8,9">
     *
     * use \PHPCore\Request;
     * 
     * // $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
     * 
     * // Get by key
     * echo Request::agent('browser'); // 'Chrome'
     * var_dump(Request::agent('istablet')); // false
     * 
     * </code>
     *
     * @param ?string $key The key of the capability data item to retrieve
     *
     * @return mixed The request capability or the entire capability object
     */
    public function agent(?string $key = null): mixed
    {
        if ( ! isset($this->Agent)) {
            return null;
        }

        if (isset($key) && isset($this->Agent)) {
            return $this->Agent->$key ?? null;
        }

        return $this->Agent;
    }

    /**
     * Get data from request body
     *
     * Will parsed the request body based on the format, then return data from the parsed body by a
     * given **$key** for data passed via the HTTP POST method. The option **$filter** and
     * **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.
     *
     * If **$key** is not passed the request body be returned and the **$filter** and **$options**
     * will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the ``filter_var()``
     *          function.
     *
     * @example Get data from request body
     * <code linenos="true" emphasize-lines="8,9">
     *
     * use \PHPCore\Request;
     * 
     * // $_POST = '{ "name": "Smith", "age": "22" }'
     * 
     * // Get by key
     * echo Request::body('name'); // 'Smith'
     * var_dump(Request::body('name', FILTER_VALIDATE_INT)); // 22
     * 
     * </code>
     *
     * @param ?string $key The key of the body's data to retrieve
     * @param ?int $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise disjunction of flags
     *
     * @return mixed The requested data item
     */
    public function body(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
    {
        static $body;

        if ( ! isset($body)) {
            if ($rawBody = @file_get_contents('php:/'.'/input')) {
                $body = match ($this->format()) {
                    'xml'   => @simplexml_load_string($rawBody),
                    'json'  => @json_decode($rawBody),
                    'yaml'  => @yaml_parse($rawBody),
                    null    => null, // BUG: Need due to but see - https://github.com/php/php-src/issues/11134
                    default => null,
                } ?? $_POST;
            }
        }

        if (isset($key)) {
            $value = match (true) {
                is_array($body)  => $body[$key] ?? null,
                is_object($body) => $body->$key ?? null,
                null             => null, // BUG: Need due to but see - https://github.com/php/php-src/issues/11134
                default          => null,
            };
        } else {
            return $body;
        }

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get data from HTTP cookie
     *
     * Will return data from cookie by a given **$key** for data passed via HTTP Cookies. The option
     * **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the
     * value is returned.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.
     *
     * @example Get data from HTTP cookie
     * <code linenos="true" emphasize-lines="7,8">
     *
     * use \PHPCore\Request;
     * 
     * // $_COOKIE = [ 'OFFSET' => 1, 'ORDER' => 'asc' ]
     * 
     * echo Request::cookie('ORDER'); // 'asc'
     * var_dump(Request::cookie('OFFSET', FILTER_VALIDATE_INT)); // 1
     * 
     * </code>
     *
     * @param string $key The key of the body's data to retrieve
     * @param ?int $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise disjunction of flags
     *
     * @return mixed The requested data item
     */
    public function cookie(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        $value = $this->Cookies[$key] ?? null;

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get file from request
     *
     * Will return the file by a given **$key** for the files that was uploaded via the HTTP POST
     * method using the ``$_FILES`` superglobal variable.
     *
     * @example Get file from request
     * <code linenos="true" emphasize-lines="14,15">
     *
     * use \PHPCore\Request;
     *
     * // $_FILES['test'] = [
     * //     'name'      => 'sample.pdf.png',
     * //     'full_path' => 'sample.pdf.png',
     * //     'type'      => 'image/png',
     * //     'tmp_name'  => '/tmp/php059gDH',
     * //     'error'     => 0,
     * //     'size'      => 3028
     * // ];
     * 
     * echo Request::file('test')->type; // 'image/png'
     * echo Request::file('test')->trueType(); // 'application/pdf'
     * 
     * </code>
     *
     * @param string $key The key of the file to retrieve
     *
     * @return ?object RequestFile object
     */
    public function file(string $key): ?object
    {
        static $request_files;

        if (empty($_FILES[$key])) {
            return null;
        }

        if ( ! isset($request_files[$key])) {
            $request_files[$key] = new RequestFile($_FILES[$key]);
        }

        return $request_files[$key];
    }

    /**
     * Get files from request
     *
     * Will return an array of files for a given **$key** that were uploaded via the HTTP POST
     * method using the ``$_FILES`` superglobal variable.
     *
     * @example Get files from request
     * <code linenos="true" emphasize-lines="14,15">
     *
     * use \PHPCore\Request;
     *
     * // $_FILES['test'] = [
     * //     'name'      => [ 'sample_1.pdf.png', 'sample_2.csv' ],
     * //     'full_path' => [ 'sample_1.pdf.png', 'sample_2.csv' ],
     * //     'type'      => [ 'image/png', text/csv', ],
     * //     'tmp_name'  => [ '/tmp/php059gDH', '/tmp/phpWGy7GA' ],
     * //     'error'     => [ 0, 0 ],
     * //     'size'      => [ 3028, 1037 ],
     * // ];
     * 
     * echo Request::file('test')[0]->name; // 'sample_1.pdf.png'
     * echo Request::file('test')[1]->name; // 'sample_2.csv'
     * 
     * </code>
     *
     * @param string $key The key of the array of files to retrieve
     *
     * @return array Array of RequestFile objects
     */
    public function files(string $key): array
    {
        static $request_files;

        if (empty($_FILES[$key])) {
            return [];
        }

        if ( ! isset($request_files[$key])) {
          $files = [];
          foreach ($_FILES[$key] as $param => $items) {
              foreach ($items as $index => $value) {
                  $files[$index][$param] = $value;
              }
          }
          foreach ($files as $index => $file) {
              $request_files[$key][$index] = new RequestFile($file);
          }
        }

        return $request_files[$key];
    }

    /**
     * Get the requested format
     *
     * This method will return the request format by first looking at the requested CONTENT_TYPE, if
     * unknown then it will attempt to decipher using the REQUEST_URI extention. If format cannot be
     * determine then the default_format set in the INI will be used.
     *
     * @example Get the requested format
     * <code linenos="true" emphasize-lines="7,10">
     *
     * use \PHPCore\Request;
     *
     * // $_SERVER['REQUEST_URI'] = '/test.php'
     * // $_SERVER['CONTENT_TYPE'] = 'application/json'
     * echo Request::format(); // 'json'
     *
     * // $_SERVER['REQUEST_URI'] = '/test.csv'
     * echo Request::format(); // 'csv'
     *
     * </code>
     *
     * @return string Format extention
     */
    public function format(): string
    {
        return $this->Format;
    }

    /**
     * Get data from request header
     *
     * Will return data from the HTTP request headers for a given **$key**. The option **$filter**
     * and **$options** parameters may be given to invoke ``filter_var()`` before the value is
     * returned.
     *
     * The key will be searched for both without then with the prefix "x-" to be compatiable with
     * older conventions. Therfore there is no need include the prefix "x-" in your code moving
     * forward.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the ``filter_var()``
     * function.
     *
     * @example Get data from request header
     * <code linenos="true" emphasize-lines="14,15,16,18">
     *
     * use \PHPCore\Request;
     *
     * // Request Headers
     * //   Accept-Encoding: gzip, deflate
     * //   Accept-Language: en-US,en;q=0.9
     * //   Connection: keep-alive
     * //   Content-Length: 0
     * //   User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36
     * //   x-custom-header-1: Random Text
     * //   x-custom-header-2: 12345
     * 
     * echo Request::header('accept-encoding'); // 'gzip, deflate'
     * echo Request::header('custom-header-1'); // 'Random Text'
     * echo Request::header('x-custom-header-1'); // 'Random Text'
     * 
     * var_dump(Request::header('custom-header-2', FILTER_VALIDATE_INT)); // 12345
     * 
     * </code>
     *
     * @param string $key The key of the header's data to retrieve
     * @param ?int $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise disjunction of flags
     *
     * @return mixed The requested header item
     */
    public function header(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        $value = match (true) {
            isset($this->Headers[strtoupper($key)])      => $this->Headers[strtoupper($key)]      ?? null,
            isset($this->Headers['X-'.strtoupper($key)]) => $this->Headers['X-'.strtoupper($key)] ?? null,
            null    => null, // BUG: Need due to but see - https://github.com/php/php-src/issues/11134
            default => null,
        };

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get requester ip address
     *
     * // TODO: SEE IF THIS IS NEED AND MOVE DOCUMENTATION
     *
     * This method will return the requester's ip address via the designated ``$_SERVER`` param that
     * contains the requester's IP Address. This is normally REMOTE_ADDR or HTTP_X_FORWARDED_FOR and
     * can be configured in the phpcore.ini file.
     *
     * @note Returns **false** if ``$_SERVER`` param is not set or the value does not pass the
     *       ``FILTER_VALIDATE_IP`` check.
     *
     * @note Note changing the ``request.ip_var`` after the first call will have no effect since the
     *       value is cached. A new instance is required should this action be required.
     *
     * @example Get requester ip address
     * <code linenos="true" emphasize-lines="10,12,16">
     *
     * use \PHPCore\Request;
     *
     * // $_SERVER['REMOTE_ADDR'] = '10.0.0.1'
     * // $_SERVER['HTTP_X_FORWARDED_FOR'] = '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
     *
     * $req = new Request();
     * core_ini_set('request.ip_var', 'REMOTE_ADDR');
     * echo $req->ip(); // '10.0.0.1'
     * core_ini_set('request.ip_var', 'HTTP_X_FORWARDED_FOR');
     * echo $req->ip(); // '10.0.0.1'
     *
     * $req = new Request();
     * core_ini_set('request.ip_var', 'HTTP_X_FORWARDED_FOR');
     * echo $req->ip(); // '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
     *
     * </code>
     *
     * @return string|false IP Address of requester
     */
    public function ip(): string|false
    {
        return $this->IpAddress;
    }

    /**
     * Get parameter from requested URI
     *
     * This method will return the variable passed to the current script via the URL parameters
     * (aka. query string) by a given **$key** using ``$_GET`` superglobal varable. If the key is
     * not passed then an array of all the variables will be returned.
     *
     * If **$key** is not passed the entire query be returned and the **$filter** and **$options**
     * will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the ``filter_var()``
     *          function.
     *
     * @example Get parameter from requested URI
     * <code linenos="true" emphasize-lines="7,9,10">
     *
     * use \PHPCore\Request;
     *
     * // $_SERVER['REQUEST_URI'] = '/index.php?text=abc&num=12345'
     * 
     * var_dump(Request::param()); // [ "text" => "abc", "num" => "12345" ]
     * 
     * var_dump(Request::param('text')); // 'abc'
     * var_dump(Request::param('num', FILTER_VALIDATE_INT)); // 12345
     *
     * </code>
     *
     * @param ?string $key The key of the query to retrieve
     * @param ?int $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise disjunction of flags
     *
     * @return mixed The requested query item
     */
    public function param(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
    {
        if (isset($key)) {
            $value = $_GET[$key] ?? null;
        } else {
            return $_GET;
        }

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get request ID
     *
     * // TODO: SEE IF THIS IS NEED AND MOVE DOCUMENTATION
     *
     * Gets the unique identifier based on the **REQUEST_TIME_FLOAT**,
     * ``Request::ip()`` and the **REQUEST_URI**.
     *
     * @example Get request ID
     * <code linenos="true" emphasize-lines="9">
     *
     * use \PHPCore\Request;
     *
     * // $_SERVER['REQUEST_TIME_FLOAT'] = 1681363597.2922
     * // $_SERVER['REMOTE_ADDR'] = '10.0.0.101'
     * // $_SERVER['REQUEST_URI'] = '/test.php'
     * 
     * echo Request::requestId(); // '9e86384b69d5abe885fe33baff74bf37'
     *
     * </code>
     *
     * @return string Request ID
     */
    public function requestId(): string
    {
        return $this->requestId;
    }

    /**
     * Get segment from requested URI
     *
     * This method will return a segment of the requested URI with a given **$pos** using the
     * **REQUEST_URI**.
     *
     * If **$pos** is not passed the entire segment array will be returned and the **$filter** and
     * **$options** will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the ``filter_var()``
     *          function.
     *
     * @example Get segment from requested URI
     * <code linenos="true" emphasize-lines="7,9,10,13">
     *
     * use \PHPCore\Request;
     *
     * // $_SERVER['REQUEST_URI'] = '/sections/articles/12345.html'
     *
     * var_dump(Request::segment()); // [ "sections", "articles", "12345" ]
     *
     * var_dump(Request::segment(1)); // 'articles'
     * var_dump(Request::segment(2, FILTER_VALIDATE_INT)); // 12345
     *
     * // phpcore.ini: request.segment_offset = 1
     * var_dump(Request::segment(0)); // 'articles'
     *
     * </code>
     *
     * @param ?int $pos The pos index of the path to retrieve
     * @param ?int $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise disjunction of flags
     *
     * @return mixed The requested segment item
     */
    public function segment(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
    {
        static $pathArray;

        if ( ! isset($pathArray)) {
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, '/') === 0) {
                $uri = substr($uri, 1);
            }
            $pathArray = explode('/', strtok(strtok($uri, '?'), '.'));

            $segment_offset = core_ini_get('request.segment_offset');
            if ( ! empty($segment_offset)) {
                $pathArray = array_slice($pathArray, intval($segment_offset));
            }

        }

        if (isset($pos)) {
            $value = $pathArray[$pos] ?? null;
        } else {
            return $pathArray;
        }

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
