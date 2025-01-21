<?php declare(strict_types=1);
/**
 * PHPCore - Request
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-21
 */

namespace PHPCore;
use PHPCore\Exceptions\RequestException;

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
 * @refence PHP filter variable:https://www.php.net/manual/en/function.filter-var.php
 * @refence PHP list of validate filters:https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
 * @refence PHP list of sanitize filters:https://www.php.net/manual/en/filter.constants.php#constant.filter-sanitize-string
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
        'request.segment_offset' => [
            'type'      => 'int',
            'default'   => '0',
        ],
        'request.ip_server_params' => [
            'type'      => 'array',
            'default'   => [
                'SSH_CONNECTION',
                'REMOTE_ADDR',
            ],
        ],
        'request.input_stream' => [
            'type'      => 'string',
            'default'   => 'php://input',
        ],
    ];

    // ---------------------------------------------------------------------

    /**
     * Get request agent capabilities
     *
     * Attempts to determine the capabilities of the user's browser by looking
     * up the browser's information in the browscap.ini file. If the optional
     * **$key** is not provided the entire capabilities object will be returned.
     *
     * @note Returns ``null`` if get_browser() fails or requested capability is
     *       unknown.
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
     * Get data from request body
     *
     * Will parsed the request body based on the format, then return data from
     * the parsed body by a given **$key** for data passed via the HTTP POST
     * method. The option **$filter** and **$options** parameters may be given
     * to invoke ``filter_var()`` before the value is returned.
     *
     * @note If **$key** is not passed the request body be returned and the
     *       **$filter** and **$options** will be ignored.
     * @note The default for **$filter** is **FILTER_DEFAULT**, which is an
     *       alias of **FILTER_UNSAFE_RAW**. This will result in no filtering
     *       taking place by default.
     *
     * @seealso `PHP list of validate filters`_ - PHP list of validate filters.
     * @seealso `PHP list of sanitize filters`_ - PHP list of sanitize filters.
     * @seealso `PHP filter variable`_ - Information on the operation of the
     *          PHP ``filter_var()`` function.
     *
     * @example Get data from request body
     * <code linenos="true" emphasize-lines="7-9,14-15">
     *
     * use \PHPCore\Request;
     *
     * $_POST = [ 'num' => 123, 'text' => 'abc'];
     *
     * var_dump(Request::getBody('text')); // 'abc'
     * var_dump(Request::getBody('num')); // '123'
     * var_dump(Request::getBody()); // [ 'text' => 'abc', 'num' => '123' ]
     *
     * $_SERVER['CONTENT_TYPE'] = 'text/json';
     * // php://input <= {"num":456, "text":"John"}
     *
     * var_dump(Request::getBody('text')); // 'John'
     * var_dump(Request::getBody('num', FILTER_VALIDATE_INT)); // 456
     * 
     * </code>
     *
     * @param ?string $key Key of the body to retrieve.
     * @param int $filter The filter to apply. Can be a validation filter by
     *                    using one of the **FILTER_VALIDATE_*** constants, a
     *                    sanitization filter by using one of the
     *                    **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a
     *                    custom filter by using **FILTER_CALLBACK**.
     * @param array|int $options Either an associative array of options, or a
     *                           bitmask of filter flag constants
     *                           **FILTER_FLAG_***. If the filter accepts
     *                           options, flags can be provided by using the
     *                           "flags" field of array.
     * @return mixed The filtered value from the body or ``null`` if the **$key**
     *               does not exist.
     */
    public static function getBody(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        static $body;
        $format = self::getFormat();

        if ( ! isset($body)) {
            if ( ! empty($_POST)) {
                $body = $_POST;
            } else {
                $stream = Config::get('request.input_stream');
                if ($format === 'csv') {
                    $body = csv_parse_file($stream);
                } elseif ($rawBody = @file_get_contents($stream)) {
                    $body = match ($format) {
                        'xml'   => @simplexml_load_string($rawBody),
                        'json'  => @json_decode($rawBody),
                        'yaml'  => @yaml_parse($rawBody),
                        'csv'   => @str_getcsv($rawBody),
                        default => null,
                    };
                }
            }
        }

        if (isset($key)) {
            if ($format === 'csv') {
                return $body[$key] ?? null;
            } else {
                $value = match (true) {
                    is_array($body)  => $body[$key] ?? null,
                    is_object($body) => $body->$key ?? null,
                    default          => null,
                };
            }
        } else {
            return $body;
        }

        if ( ! isset($value)) {
            return null;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Get data from HTTP cookie
     *
     * Will return data from the HTTP cookie for a given **$key** using the
     * ``$_HEADER`` superglobal varable. The optional **$filter** and
     * **$options** parameters may be given to invoke ``filter_var()`` before
     * the value is returned.
     *
     * @note If **$key** is not passed the cookie array be returned and the
     *       **$filter** and **$options** will be ignored.
     * @note The default for **$filter** is **FILTER_DEFAULT**, which is an
     *       alias of **FILTER_UNSAFE_RAW**. This will result in no filtering
     *       taking place by default.
     *
     * @seealso `PHP list of validate filters`_ - PHP list of validate filters.
     * @seealso `PHP list of sanitize filters`_ - PHP list of sanitize filters.
     * @seealso `PHP filter variable`_ - Information on the operation of the
     *          PHP ``filter_var()`` function.
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
     * @param ?string $key Key of the cookie to retrieve.
     * @param int $filter The filter to apply. Can be a validation filter by
     *                    using one of the **FILTER_VALIDATE_*** constants, a
     *                    sanitization filter by using one of the
     *                    **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a
     *                    custom filter by using **FILTER_CALLBACK**.
     * @param array|int $options Either an associative array of options, or a
     *                           bitmask of filter flag constants
     *                           **FILTER_FLAG_***. If the filter accepts
     *                           options, flags can be provided by using the
     *                           "flags" field of array.
     * @return mixed The filtered value from the cookie or ``null`` if the
     *               **$key** does not exist.
     */
    public static function getCookie(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        if (is_null($key)) {
            return $_COOKIE;
        }
        if ( ! isset($_COOKIE[$key])) {
            return null;
        }
        return filter_var($_COOKIE[$key], $filter, $options);
    }

    /**
     * Get file from request
     *
     * Will return the file by a given **$key** from the files that were
     * uploaded via the HTTP POST method using the ``$_FILES`` superglobal
     * variable.
     *
     * @example Get file from request
     * <code linenos="true" emphasize-lines="18-22,24-28">
     *
     * use \PHPCore\Request;
     * use \PHPCore\RequestFile;
     * use \PHPCore\Exceptions\RequestException;
     *
     * $_FILE = [
     *     'file_upload' => [
     *         'name' => 'test.csv',
     *         'full_path' => 'test.json',
     *         'type' => 'text/csv',
     *         'tmp_name' => '/data/test/test.csv',
     *         'error' => 0,
     *         'size' => 27
     *     ]
     * ];
     *
     * $file = Request::getFile('file_upload');
     * var_dump($file->getContents()); // '{"name":"Test","value":123}'
     * var_dump($file->isTrueType()); // false
     * var_dump($file->error); // 9
     * var_dump($file->getErrorMessage()); // 'File was not uploaded via HTTP POST'
     *
     * try {
     *     $file = Request::getFile('file_upload', RequestFile::EXCEPTION_ON_ERROR);
     * } catch (RequestException $e) {
     *     var_dump($file->getErrorMessage()); // 9
     * }
     * </code>
     *
     * @param string $key The key of the file to retrieve.
     * @param int $flags Bitwise flags for this method
     * @return ?object RequestFile object or ``null`` if the **$key** does not
     *                 exist.
     */
    public static function getFile(string $key, int $flags = 0): ?object
    {
        static $files = [];

        if (empty($_FILES[$key])) {
            return null;
        }

        if ( ! isset($files[$key])) {
            $files[$key] = new RequestFile($_FILES[$key], $flags);
        }

        return $files[$key];
    }

    /**
     * Get files from request
     *
     * Will return an array of files for a given **$key** that were uploaded via
     * the HTTP POST method using the ``$_FILES`` superglobal variable.
     *
     * @example Get files from request
     * <code linenos="true" emphasize-lines="34-37">
     *
     * use \PHPCore\Request;
     *
     * $_FILE = [
     *     'file_upload' => [
     *         'name' => [
     *             0 => 'test.csv',
     *             1 => 'test.json'
     *         ],
     *         'full_path' => [
     *             0 => 'test.csv',
     *             1 => 'test.json'
     *         ],
     *         'type' => [
     *             0 => 'text/csv',
     *             1 => 'text/csv'
     *         ]
     *         'tmp_name' => [
     *             0 => '/data/test/test.csv',
     *             1 => '/tmp/phpAKmVxj'
     *         ],
     *         'error' => [
     *             0 => 0,
     *             0 => 0
     *         ],
     *         'size' => [
     *             0 => 41,
     *             0 => 27
     *         ]
     *     ]
     * ];
     *
     * $files = Request::getFiles('file_upload');
     * var_dump($files[1]->getContents()); // '{"name":"Test","value":123}'
     * var_dump($files[0]->error); // 9
     * var_dump($files[1]->error); // 0
     *
     * </code>
     *
     * @param string $key The key of the array of files to retrieve.
     * @param int $flags Bitwise flags for this method
     * @return ?array Array of RequestFile objects
     */
    public static function getFiles(string $key, int $flags = 0): ?array
    {
        static $files = [];

        if (empty($_FILES[$key])) {
            return null;
        }

        if ( ! isset($files[$key])) {
            $tmp_files = [];
            foreach ($_FILES[$key] as $param => $items) {
                foreach ($items as $index => $value) {
                    $tmp_files[$index][$param] = $value;
                }
            }
            foreach ($tmp_files as $index => $file) {
                $files[$key][$index] = new RequestFile($file, $flags);
            }
            unset($tmp_files);
        }

        return $files[$key];
    }

    /**
     * Get format from request
     *
     * Will return the format from an HTTP request by first looking at the
     * the ``$_HEADER`` superglobal varable for first the ``CONTENT_TYPE`` and
     * then the ``REQUEST_URI`` to determine the requested format. If format
     * cannot be determine then the ``request.default_format`` declared
     * in the phpcore.ini will be used.
     *
     * @example Get format from request
     * <code linenos="true" emphasize-lines="12,15,18">
     *
     * use \PHPCore\Request;
     * use \PHPCore\Config;
     *
     * Config.set('request.default_format', 'text');
     * Config.set('request.supported_formats', [ 'text', 'xml', 'json' ]);
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
            'text/xml',
            'application/xml',
            'application/x-www-form-urlencoded' => 'xml',

            'text/json',
            'application/json' => 'json',

            'text/yaml',
            'application/x-yaml' => 'yaml',

            'text/csv' => 'csv',
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
     * Get HTTP data from request header
     *
     * Will return data from the HTTP request headers for a given **$key** using
     * the ``$_HEADER`` superglobal varable. The optional **$filter** and
     * **$options** parameters may be given to invoke ``filter_var()`` before
     * the value is returned.
     *
     * The **$key** will be searched for both without then with the prefix "X-"
     * to be compatiable with older conventions. Therfore there is no need
     * include the prefix "X-" in your code moving forward. If both are present
     * the one without the "X-" will be returned.
     *
     * @note Do not include the "HTTP" prefix to the **$key**.
     * @note The default for **$filter** is **FILTER_DEFAULT**, which is an
     *       alias of **FILTER_UNSAFE_RAW**. This will result in no filtering
     *       taking place by default.
     *
     * @seealso `PHP list of validate filters`_ - PHP list of validate filters.
     * @seealso `PHP list of sanitize filters`_ - PHP list of sanitize filters.
     * @seealso `PHP filter variable`_ - Information on the operation of the
     *          PHP ``filter_var()`` function.
     *
     * @example Get data from request header
     * <code linenos="true" emphasize-lines="9-11">
     *
     * use \PHPCore\Request;
     *
     * $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
     * $_SERVER['HTTP_CUSTOM_HEADER'] = '1';
     * $_SERVER['HTTP_X_CUSTOM_HEADER'] = '2';
     *
     * echo Request::getHeader('accept-encoding'); // 'gzip, deflate'
     * echo Request::getHeader('custom-header'); // '1'
     * var_dump(Request::getHeader('x-custom-header', FILTER_VALIDATE_INT)); // 1
     *
     * </code>
     *
     * @param ?string $key Key of the header to retrieve.
     * @param int $filter The filter to apply. Can be a validation filter by
     *                    using one of the **FILTER_VALIDATE_*** constants, a
     *                    sanitization filter by using one of the
     *                    **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a
     *                    custom filter by using **FILTER_CALLBACK**.
     * @param array|int $options Either an associative array of options, or a
     *                           bitmask of filter flag constants
     *                           **FILTER_FLAG_***. If the filter accepts
     *                           options, flags can be provided by using the
     *                           "flags" field of array.
     * @return mixed The filtered value from the header or ``null`` if the
     *               **$key** does not exist.
     */
    public static function getHttpHeader(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {

        $raw_http_headers = array_filter(
            $_SERVER,
            fn($k) => str_starts_with($k, 'HTTP_'),
            ARRAY_FILTER_USE_KEY
        );
        $http_headers = [];
        foreach ($raw_http_headers as $k => $v) {
            $http_headers[substr($k, 5)] = $v;
        }

        if (is_null($key)) {
            return $http_headers;
        }

        $key = strtoupper($key);

        $search = [$key];
        if (strpos($key, 'X_') !== 0) {
            $search[] = "X_$key";
        }

        $key_search = array_find(
            $search,
            fn($v, $k) => isset($http_headers[$v])
        );

        if ( ! isset($http_headers[$key_search])) {
            return null;
        }
        return filter_var($http_headers[$key_search], $filter, $options);
    }

    /**
     * Get IP address
     *
     * Returns the requester's ip address by the designated ``$_SERVER`` param
     * that contains the requester's IP Address. This is normally
     * ``REMOTE_ADDR`` or ``HTTP_X_FORWARDED_FOR`` and can be configured in the
     * phpcore.ini file via the ``request.ip_server_params`` option.
     *
     * @note Will return ``null`` if ``$_SERVER`` param is not set or ``false``
     *       if the **$check_valid** is true and it does not pass the
     *       ``FILTER_VALIDATE_IP`` check.
     *
     * @example Get data from HTTP cookie
     * <code linenos="true" emphasize-lines="8,13-14">
     *
     * use \PHPCore\Request;
     *
     * $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
     * $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.2';
     *
     * echo Request::getIpAddress(); // '10.0.0.1'
     *
     * $_SERVER['REMOTE_ADDR'] = '10.0.1';
     * $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
     *
     * var_dump(Request::getIpAddress()); // '10.0.1'
     * var_dump(Request::getIpAddress(true)); // false
     *
     * </code>
     *
     * @param bool $check_valid Check using ``FILTER_VALIDATE_IP`` filter.
     * @return ?string IP address makeing request.
     */
    public static function getIpAddress($check_valid = true): mixed {
        $ip_server_params = Config::get('request.ip_server_params');
        if (empty($ip_server_params)) {
            throw new RequestException(
                NULL,
                RequestException::CONFIG_ERR_IP_SVR_PARM
            );
        }

        $ip_server_param = array_find(
            $ip_server_params,
            fn($v, $k) => isset($_SERVER[$v])
        );

        $ip_address = explode(' ', $_SERVER[$ip_server_param] ?? '')[0];

        if (empty($ip_address)) {
            return null;
        } elseif ($check_valid) {
            return filter_var($ip_address, FILTER_VALIDATE_IP);
        } else {
            return $ip_address;
        }
    }

    /**
     * Get parameter from requested URI
     *
     * This method will return the variable passed to the current script via the
     * URL parameters (aka. query string) by a given **$key** using ``$_GET``
     * superglobal varable. If the optional **$key** is not provided then an
     * array of all the URL parameters will be returned.
     *
     * @note If **$key** is not provided the **$filter** and **$options**
     *       arguments will be ignored.
     * @note The default for **$filter** is **FILTER_DEFAULT**, which is an
     *       alias of **FILTER_UNSAFE_RAW**. This will result in no filtering
     *       taking place by default.
     *
     * @seealso `PHP list of validate filters`_ - PHP list of validate filters.
     * @seealso `PHP list of sanitize filters`_ - PHP list of sanitize filters.
     * @seealso `PHP filter variable`_ - Information on the operation of the
     *          PHP ``filter_var()`` function.
     *
     * @example Get parameter from requested URI
     * <code linenos="true" emphasize-lines="7-9">
     *
     * use \PHPCore\Request;
     *
     * $_SERVER['REQUEST_URI'] = '/index.php?text=abc&num=12345';
     *
     * var_dump(Request::getParameter()); // [ 'text' => 'abc', 'num' => '12345' ]
     * var_dump(Request::getParameter('text')); // 'abc'
     * var_dump(Request::getParameter('num', FILTER_VALIDATE_INT)); // 12345
     *
     * </code>
     *
     * @param ?string $key Key of the query parameter to retrieve.
     * @param int $filter The filter to apply. Can be a validation filter by
     *                    using one of the **FILTER_VALIDATE_*** constants, a
     *                    sanitization filter by using one of the
     *                    **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a
     *                    custom filter by using **FILTER_CALLBACK**.
     * @param array|int $options Either an associative array of options, or a
     *                           bitmask of filter flag constants
     *                           **FILTER_FLAG_***. If the filter accepts
     *                           options, flags can be provided by using the
     *                           "flags" field of array.
     * @return mixed The filtered value from the query parameter or ``null`` if
     *               the **$key** does not exist.
     */
    public static function getParameter(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        if (isset($key)) {
            if ( ! isset($_GET[$key])) {
                return null;
            }
            return filter_var($_GET[$key], $filter, $options);
        } else {
            return $_GET;
        }
    }

    /**
     * Get segment from requested URI
     *
     * This method will return a segment of the requested URI with a given
     * **$pos** using the **REQUEST_URI** from the ``$_GET`` superglobal
     * varable.
     *
     * @note If **$pos** is not passed the entire segment array will be returned
     *       and the **$filter** and **$options** will be ignored.
     * @note The default for **$filter** is **FILTER_DEFAULT**, which is an
     *       alias of **FILTER_UNSAFE_RAW**. This will result in no filtering
     *       taking place by default.
     *
     * @seealso `PHP list of validate filters`_ - PHP list of validate filters.
     * @seealso `PHP list of sanitize filters`_ - PHP list of sanitize filters.
     * @seealso `PHP filter variable`_ - Information on the operation of the
     *          PHP ``filter_var()`` function.
     *
     * @example Get segment from requested URI
     * <code linenos="true" emphasize-lines="8-12,15">
     *
     * use \PHPCore\Request;
     * use \PHPCore\Config;
     *
     * $_SERVER['REQUEST_URI'] = '/sections/articles/12345.html';
     *
     * var_dump(Request::getSegment()); // [ 'sections', 'articles', '12345' ]
     * var_dump(Request::getSegment(0)); // 'sections'
     * var_dump(Request::getSegment(4)); // null
     * var_dump(Request::getSegment(2, FILTER_VALIDATE_INT)); // 12345
     * var_dump(Request::getSegment(1, FILTER_VALIDATE_INT)); // false
     *
     * Config.set('request.segment_offset', 1);
     * var_dump(Request::segment(0)); // 'articles'
     *
     * </code>
     *
     * @param ?int $pos The pos index of the path to retrieve.
     * @param int $filter The filter to apply. Can be a validation filter by
     *                    using one of the **FILTER_VALIDATE_*** constants, a
     *                    sanitization filter by using one of the
     *                    **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a
     *                    custom filter by using **FILTER_CALLBACK**.
     * @param array|int $options Either an associative array of options, or a
     *                           bitmask of filter flag constants
     *                           **FILTER_FLAG_***. If the filter accepts
     *                           options, flags can be provided by using the
     *                           "flags" field of array.
     * @return mixed The filtered value from the requested segment item or
     *               ``null`` if the **$key** does not exist.
     */
    public static function getSegment(
        ?int $pos = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        $offset = Config::get('request.segment_offset');

        $uri = $_SERVER['REQUEST_URI'];

        $path = explode('.', parse_url($uri, PHP_URL_PATH))[0];
        $parts = explode('/', substr($path, 1));
        $segments = array_slice($parts, $offset);

        if (is_null($pos)) {
            return $segments;
        }

        if ($pos < 0 || ! isset($segments[$pos])) {
            return null;
        }

        return filter_var($segments[$pos], $filter, $options);
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
