<?php declare(strict_types=1);
/**
 * PHPCore - Request
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-05
 */

namespace PHPCore;

// -----------------------------------------------------------------------------

/**
 * Request Class
 *
 * The Request class is used to simplify working with data send via the http
 * protocal.
 *
 * @seealso `PHPCore Request Functions`_ - Simplified functions that interface
 *          directly with the `PHPCore Request Class`_.
 *
 * @refence PHPCore Request Class: ../classes/request.html
 * @refence PHPCore Request Functions: ../functions/request.html
 * @refence PHP Filter Variable:https://www.php.net/manual/en/function.filter-var.php
 * @refence PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
 */
#[Test('tests/RequestTest.php')]
#[Documentation('docs/classes/request.rst')]
final class Request
{
    /**
     * Agent data types.
     *
     * @ignore
     * @const array
     */
    public const AGENT_DATA_TYPES = [
        'activexcontrols'       => 'bool',
        'alpha'                 => 'bool',
        'aolversion'            => 'int',
        'backgroundsounds'      => 'bool',
        'beta'                  => 'bool',
        'browser_bits'          => 'int',
        'cookies'               => 'bool',
        'crawler'               => 'bool',
        'cssversion'            => 'int',
        'frames'                => 'bool',
        'javascript'            => 'bool',
        'iframes'               => 'bool',
        'isanonymized'          => 'bool',
        'isfake'                => 'bool',
        'ismobiledevice'        => 'bool',
        'ismodified'            => 'bool',
        'issyndicationreader'   => 'bool',
        'istablet'              => 'bool',
        'javaapplets'           => 'bool',
        'majorver'              => 'int',
        'minorver'              => 'int',
        'platform_bits'         => 'int',
        'tables'                => 'bool',
        'vbscript'              => 'bool',
        'win16'                 => 'bool',
        'win32'                 => 'bool',
        'win64'                 => 'bool',
    ];

    /**
     * PHPCore ini config options.
     *
     * @ignore
     * @const array
     */
    public const CONFIG_OPTIONS = [
        'request.default_format' => [
            'type'      => 'string',
            'default'   => 'json',
        ],
        'request.supported_formats' => [
            'type'      => 'array',
            'default'   => [
                'json',
                'yaml',
                'xml',
            ],
        ],
        'request.ip_server_params' => [
            'type'      => 'array',
            'default'   => [
                'SSH_CONNECTION',
                'REMOTE_ADDR',
            ],
        ],
    ];

    // ---------------------------------------------------------------------

    /**
     * Request ID
     *
     * This is the the unique identifier that is assigned when the Request class
     * is constructed.
     *
     * @example Get request ID
     * <code linenos="true" emphasize-lines="10">
     *
     * use \PHPCore\Request;
     * $request = Request::getRequest();
     *
     * // $_SERVER['REQUEST_TIME_FLOAT'] = 1681363597.2922
     * // $_SERVER['REMOTE_ADDR'] = '10.0.0.101'
     * // $_SERVER['REQUEST_URI'] = '/test.php'
     *
     * var_dump($request->RequestId); // '9e86384b69d5abe885fe33baff74bf37'
     *
     * </code>
     *
     * @prop ?string
     */
    public readonly ?string $RequestId;

    /**
     * Request Time Start
     *
     * @prop float
     */
    public readonly float $RequestTimeStart;

    // ---------------------------------------------------------------------

    /**
     * Get request agent capabilities
     *
     * Attempts to determine the capabilities of the user's browser by looking
     * up the browser's information in the browscap.ini file. If the options
     * **$key** is not provides the entire capabilities object will be returned.
     *
     * @note Returns ``null`` if get_browser() fails or requested capability is
     * unknown.
     *
     * @example Get request agent capabilities
     * <code linenos="true" emphasize-lines="9,10">
     *
     * use \PHPCore\Request;
     *
     * $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'.
     * ' AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';
     *
     * // Get by key
     * echo Request::agent('platform'); // 'Win10'
     * var_dump(Request::agent('ismobiledevice')); // false
     *
     * </code>
     *
     * @param ?string $key The key of the capability data item to retrieve.
     * @return mixed The request capability or the entire capability object.
     */
    public static function getAgent(?string $key = null): mixed
    {
        static $agent;

        if (
            isset($_SERVER['HTTP_USER_AGENT']) && !isset($agent)
        ) {
            $agent = @get_browser($_SERVER['HTTP_USER_AGENT']);
            if ($agent !== false) {
                foreach (self::AGENT_DATA_TYPES as $field => $type) {
                    if (isset($agent->$field)) {
                        $agent->$field = match ($type) {
                            'int'   => intval($agent->$field),
                            'bool'  => boolval($agent->$field),
                            default => $agent->$field,
                        };
                    }
                }
            }
        }

        if (isset($key) && ! empty($agent)) {
            return $agent->$key ?? null;
        }

        return $agent ?? null;
    }

    /**
     * Get data from HTTP cookie
     *
     * Will return data from cookie by a given **$key** for data passed via HTTP
     * Cookies. The option **$filter** and **$options** parameters may be given
     * to invoke ``filter_var()`` before the value is returned.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
     *
     * @example Get data from HTTP cookie
     * <code linenos="true" emphasize-lines="7,8-9">
     *
     * use \PHPCore\Request;
     *
     * $_COOKIE = [ 'PaginationOffset' => 1, 'PaginationOrder' => 'asc' ]
     *
     * echo Request::getCookie('PaginationOrder'); // 'asc'
     * var_dump(Request::getCookie('PaginationOffset', FILTER_VALIDATE_INT)); // 1
     * var_dump(Request::getCookie('PaginationOrder', FILTER_VALIDATE_INT)); // 1
     *
     * </code>
     *
     * @param string $key The key of the cookie to retrieve.
     * @param ?int $filter The ID of the filter to apply.
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags.
     * @return mixed The requested cookie or ``null` if it does not exist.
     */
    public static function getCookie(
        string $key,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {
        return self::filterValue($_COOKIE[$key] ?? null, $filter, $options);
    }

    /**
     * Get format from request
     *
     * This will return the format from an HTTP request by first looking at the
     * requested ``CONTENT_TYPE``, if unknown then it will attempt to determine
     * it by using the ``REQUEST_URI`` (i.e. 'resource.json' => 'json'). If
     * format cannot be determine then the ``request.default_format`` declared
     * in the phpcore.ini will be used.
     *
     * @example Get format from request
     * <code linenos="true" emphasize-lines="12,15,18">
     *
     * use \PHPCore\Request;
     * use \PHPCore\Config;
     *
     * Config.set('request.default_format', 'text');
     * Config.set('request.supported_formats', ['text', 'xml', 'json']);
     *
     * $_SERVER['REQUEST_URI'] = '/';
     * $_SERVER['CONTENT_TYPE'] = null;
     *
     * echo Request::getFormat(); // 'csv'
     *
     * $_SERVER['REQUEST_URI'] = '/resource.xml?query=test';
     * echo Request::getFormat(); // 'xml'
     *
     * $_SERVER['CONTENT_TYPE'] = '/application/json';
     * echo Request::getFormat(); // 'json'
     *
     * </code>
     *
     * @return ?string The format that was requested.
     */
    public static function getFormat(): ?string
    {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? null;

        $format = match($content_type) {
            'application/x-www-form-urlencoded' => 'xml',
            'text/json', 'application/json'     => 'json',
            'text/yaml', 'application/x-yaml'   => 'yaml',
            'text/csv'                          => 'csv',
            null    => null,
            default => null,
        };

        if (empty($format) && isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $dot_pos = strripos($path, '.');
            if ($dot_pos !== false) {
              $format = strtolower(substr($path, $dot_pos + 1));
            }
        }

        if ( ! in_array($format, Config::get('request.supported_formats'))) {
            $format = null;
        }

        if (empty($format)) {
            $format = Config::get('request.default_format');
        }

        return $format;
    }

    /**
     * Get data from request header
     *
     * Will return data from the HTTP request headers for a given **$key**. The
     * option **$filter** and **$options** parameters may be given to invoke
     * ``filter_var()`` before the value is returned.
     *
     * The key will be searched for both without then with the prefix "X-" to be
     * compatiable with older conventions. Therfore there is no need include the
     * prefix "X-" in your code moving forward. If both are present the one
     * without the "X-" will be returned.
     *
     * @note Do not include the "HTTP_" prefix to the **$key**.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     * ``filter_var()`` function.
     *
     * @example Get data from request header
     * <code linenos="true" emphasize-lines="14,15,16,18">
     *
     * use \PHPCore\Request;
     *
     * // Request Headers
     * //   Accept-Encoding: gzip, deflate
     * //   Accept-Language: en-US,en;q=0.9
     * //   ...
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
     * @param string $key The key of the header to retrieve.
     * @param ?int $filter The ID of the filter to apply.
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags.
     * @return mixed The requested header  or ``null` if it does not exist.
     */
    public static function getHeader(
        string $key,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {

        $key = strtoupper($key);

        $search = ["HTTP_$key"];
        if (!empty($key) && $key[0] !== 'X') {
            $search[] = "HTTP_X_$key";
        }
        
        $key_2 = array_find(
            $search,
            fn($v, $k) => isset($_SERVER[$v])
        );

        return self::filterValue($_SERVER[$key_2] ?? null, $filter, $options);
    }

    /**
     * Get IP address
     *
     * Returns the requester's ip address by the designated ``$_SERVER`` param
     * that contains the requester's IP Address. This is normally
     * ``REMOTE_ADDR`` or ``HTTP_X_FORWARDED_FOR`` and can be configured in the
     * phpcore.ini file via the ``request.ip_server_params`` option.
     *
     * @note Will be **false** if ``$_SERVER`` param is not set or the value
     *       does not pass the ``FILTER_VALIDATE_IP`` check.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
     *
     * @example Get data from HTTP cookie
     * <code linenos="true" emphasize-lines="7,8">
     *
     * use \PHPCore\Request;
     *
     * $_COOKIE = [ 'PaginationOffset' => 1, 'PaginationOrder' => 'asc' ]
     *
     * echo Request::getCookie('PaginationOrder'); // 'asc'
     * var_dump(Request::getCookie('PaginationOffset', FILTER_VALIDATE_INT)); // 1
     * var_dump(Request::getCookie('PaginationOrder', FILTER_VALIDATE_INT)); // 1
     *
     * </code>
     *
     * @return ?string IP address makeing request.
     */
    public static function getIpAddress(): ?string {
        static $ip_address;

        if (isset($ip_address)) {
            return $ip_address;
        }

        $ip_server_params = Config::get('request.ip_server_params');
        if (empty($ip_server_params)) {
            throw new RequestException(
                'Empty `request.ip_server_params` in phpcore.ini'
            );
        }

        $ip_server_param = array_find(
            $ip_server_params,
            fn($v, $k) => isset($_SERVER[$v])
        );

        $ip_address = explode(' ', $_SERVER[$ip_server_param] ?? '')[0];

        return empty($ip_address) ? null : $ip_address;
    }

    // ---------------------------------------------------------------------

    /**
     * Filter value
     *
     * This method will return a filtere value if a filter is specified. If no
     * filter is specified the orginal value will be returned. 
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
     *
     * @param mixed     $value   Value to be filtered .
     * @param ?int      $filter  The ID of the filter to apply.
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags.
     * @return mixed The filtered value.
     */
    private static function filterValue(
        mixed $value,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {
        if (isset($filter)) {
            return filter_var($value, $filter, $options);
        } else {
            return $value;
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Get request object
     *
     * This method is used to retrive a previously constructed request instance
     * by a given `$request_id`.
     *
     * @throw  Exception('Error string')
     *
     * @param  ?string $request_id Request ID
     * @return ?PHPCore\Request Request instance
     */
    public static function &zGetRequest(?string $request_id = null): ?Request
    {
        if (empty(self::$Instances)) {
            $method = __METHOD__;
            throw new Exception(
                "$method cannot be invoked because no Request instances exist."
            );
        }

        if (!isset($request_id)) {
            if (count(self::$Instances) > 1) {
                $method = __METHOD__;
                throw new Exception(
                    "$method cannot be invoked without the request_id parameter with mutiple Request instances."
                );
            }
            $request_id = array_keys(self::$Instances)[0];
        }

        if (!isset(self::$Instances[$request_id])) {
            throw new Exception(
                "Request ID `$request_id` was not found."
            );
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
     * @param  array $params Parameters for request
     * @return void
     */
    public function __construct(array $params = [])
    {
        // Time Start
        if (!isset($params['request_time'])) {
            $params['request_time'] = $_SERVER['REQUEST_TIME_FLOAT'];
        }
        $this->RequestTimeStart = $params['request_time'] ?? microtime(true);

        // Cookies
        if (!isset($params['cookies']) && isset($_COOKIE)) {
            $params['cookies'] = $_COOKIE;
        }
        $this->Cookies = $params['cookies'] ?? [];

        // Headers
        if (!isset($params['headers']) && function_exists('getallheaders')) {
            $params['headers'] = getallheaders();
        }
        $this->Headers = $params['headers'] ?? [];

        // Format
        if (!isset($params['format'])) {
            $params['format'] = $this->getRequestedFormat();
        }
        $this->Format = $params['format'] ?? 'json';

        // Requester's IP address
        $ip_srv_params = phpcore_ini_get('request.ip_server_params');
        if (!isset($params['ip_address'])) {
            $ip_srv_param = array_find(
                $ip_srv_params,
                fn($i) => isset($_SERVER[$i])
            );
        }
        $this->IpAddress = $_SERVER[$ip_srv_param] ?? false;

        // Request ID
        if (!isset($params['request_id'])) {
            $time = strval($this->RequestTimeStart);
            $params['request_id'] = md5("{$time}{$ip_address}");
        }
        $this->requestId = $request_id;

        // Duplicate request instance with same request ID
        if (isset(self::$Instances[$this->requestId])) {
            $method = __METHOD__;
            trigger_error(
                "$method failed, Request ID already exists."
            , E_USER_WARNING);
        }

        self::$Instances[$this->requestId] =& $this;
    }

    // ---------------------------------------------------------------------

    /**
     * Get data from request body
     *
     * Will parsed the request body based on the format, then return data from
     * the parsed body by a given **$key** for data passed via the HTTP POST
     * method. The option **$filter** and **$options** parameters may be given
     * to invoke ``filter_var()`` before the value is returned.
     *
     * If **$key** is not passed the request body be returned and the
     * **$filter** and **$options** will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
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
     * @param ?string   $key     The key of the body's data to retrieve
     * @param ?int      $filter  The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     *
     * @return mixed The requested data item
     */
    public function getBody(
        ?string $key = null,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {
        // todo get ride of static due to mutiple request instance creations
        static $body;

        if (!isset($body)) {
            if ($rawBody = @file_get_contents('php:/' . '/input')) {
                $body = match ($this->format()) {
                    'xml'   => @simplexml_load_string($rawBody),
                    'json'  => @json_decode($rawBody),
                    'yaml'  => @yaml_parse($rawBody),
                    // BUG: Need NULL due to but see - https://github.com/php/php-src/issues/11134
                    null    => null,
                    default => null,
                } ?? $_POST;
            }
        }

        if (isset($key)) {
            $value = match (true) {
                is_array($body)  => $body[$key] ?? null,
                is_object($body) => $body->$key ?? null,
                // BUG: NULL Need due to but see - https://github.com/php/php-src/issues/11134
                null             => null,
                default          => null,
            };
        } else {
            return $body;
        }

        return $this->filterValue($value, $filter, $options);
    }

    /**
     * Get file from request
     *
     * Will return the file by a given **$key** for the files that was uploaded
     * via the HTTP POST method using the ``$_FILES`` superglobal variable.
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
    public function getFile(string $key): ?object
    {
        // todo get ride of static due to mutiple request instance creations
        static $request_files;

        if (empty($_FILES[$key])) {
            return null;
        }

        if (!isset($request_files[$key])) {
            $request_files[$key] = new RequestFile($_FILES[$key]);
        }

        return $request_files[$key];
    }

    /**
     * Get files from request
     *
     * Will return an array of files for a given **$key** that were uploaded via
     * the HTTP POST method using the ``$_FILES`` superglobal variable.
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
    public function getFiles(string $key): array
    {
        // todo get ride of static due to mutiple request instance creations
        static $request_files;

        if (empty($_FILES[$key])) {
            return [];
        }

        if (!isset($request_files[$key])) {
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
     * Get parameter from requested URI
     *
     * This method will return the variable passed to the current script via the
     * URL parameters (aka. query string) by a given **$key** using ``$_GET``
     * superglobal varable. If the key is not passed then an array of all the
     * variables will be returned.
     *
     * If **$key** is not passed the entire query be returned and the
     * **$filter** and **$options** will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
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
     * @param ?string   $key     The key of the query to retrieve
     * @param ?int      $filter  The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     *
     * @return mixed The requested query item
     */
    public function getParam(
        ?string $key = null,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {
        if (isset($key)) {
            // TODO: create $this->Params and pull from there
            $value = $_GET[$key] ?? null;
            return $this->filterValue($value, $filter, $options);
        } else {
            return $_GET;
        }
    }

    /**
     * Get segment from requested URI
     *
     * This method will return a segment of the requested URI with a given
     * **$pos** using the **REQUEST_URI**.
     *
     * If **$pos** is not passed the entire segment array will be returned and
     * the **$filter** and **$options** will be ignored.
     *
     * @seealso `PHP Types of filters`_ - List of available filters and options.
     * @seealso `PHP Filter Variable`_ - Information on the operation of the
     *          ``filter_var()`` function.
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
     * @param ?int      $pos     The pos index of the path to retrieve
     * @param ?int      $filter  The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     *
     * @return mixed The requested segment item
     */
    public function getSegment(
        ?int $pos = null,
        ?int $filter = null,
        array|int $options = 0
    ): mixed {
        static $pathArray;

        if (!isset($pathArray)) {
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, '/') === 0) {
                $uri = substr($uri, 1);
            }
            $pathArray = explode('/', strtok(strtok($uri, '?'), '.'));

            $segment_offset = phpcore_ini_get('request.segment_offset');
            if (!empty($segment_offset)) {
                $pathArray = array_slice($pathArray, intval($segment_offset));
            }
        }

        if (isset($pos)) {
            $value = $pathArray[$pos] ?? null;
            return $this->filterValue($value, $filter, $options);
        } else {
            return $pathArray;
        }
    }
}

// -----------------------------------------------------------------------------

/**
 * Request Exception Class
 *
 * The Exception class is used to throw exceptions in the Request class.
 *
 * @codeCoverageIgnore
 */
final class RequestException extends \Exception
{
    /**
     * To string
     *
     * This method is get the description of the exception.
     *
     * @ignore
     * @return string Exception description
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
