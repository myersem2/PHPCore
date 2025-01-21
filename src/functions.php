<?php declare(strict_types=1);
/**
 * PHPCore - Functions
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-05
 */

// -----------------------------------------------------------------------------

$disable_functions = [];
$disable_classes = [];
if (class_exists('\PHPCore\Config')) {
    $disable_functions = \PHPCore\Config::get('disable_functions') ?? [];
    $disable_classes = \PHPCore\Config::get('disable_classes') ?? [];
}

// -----------------------------------------------------------------------------

// TODO: RBF
function ________PHP_EARLIER_FUNCTION_SHIMS_________(){}

// PHP earlier function shims

/**
 * Returns the first element satisfying a callback function
 *
 * ``array_find()`` returns the value of the first element of an ``array`` for
 * which the given **$callback** returns ``true``. If no matching element is
 * found the function returns ``null``.
 *
 * @todo Remove after (PHP 8 >= 8.4.0)
 *
 * @param array $array The array that should be searched.
 * @param callable $callback The callback function to call to check each element
 *                           , which must be ``callback(mixed $value, mixed
 *                           $key): bool`` If this function returns ``true``,
 *                           the value is returned from ``array_find()`` and the
 *                           callback will not be called for further elements.
 * @return mixed The function returns the value of the first element for which
 *               the **$callback** returns ``true``. If no matching element is
 *               found the function returns null.
 */
if ( ! in_array('array_find', $disable_functions) ) {
    function array_find(array $array, callable $callback): mixed
    {
        foreach ($array as $key=>$item) {
            if (call_user_func_array($callback, [$item, $key])) {
                return $item;
            }
        }
        return null;
    }
}

// -----------------------------------------------------------------------------

// TODO: RBF
function ____________CLASS_ALIAS_FUNCTIONS____________(){}

// Class alias functions

/**
 * Gets the value of a configuration option
 *
 * Returns the value of the configuration option on success.
 *
 * @param string $option The configuration option name.
 * @return mixed Returns the value of the configuration option on success.
 *               Returns null if the configuration option doesn't exist.
 */
if ( ! in_array('phpcore_ini_get', $disable_functions) ) {
    function phpcore_ini_get(string $option): mixed
    {
        return \PHPCore\Config::get($option);
    }
}

/**
 * Gets all configuration options
 *
 * Returns all the registered configuration options.
 *
 * @param ?string $extension An optional extension name. If not null the
 *                           function returns only options specific for that
 *                           extension.
 * @param ?bool $details Retrieve details settings or only the current value
 *                       for each setting. Default is true (retrieve details).
 * @return ?array Returns an associative array with directive name as the array
 *                key. Returns null if the extension doesn't exist.
 */
if ( ! in_array('phpcore_ini_get_all', $disable_functions) ) {
    function phpcore_ini_get_all(
        ?string $extension = null,
        ?bool $details = true
    ): ?array
    {
        return \PHPCore\Config::getAll($extension, $details);
    }
}

/**
 * Restores the value of a configuration option
 *
 * Restores a given configuration option to its original value.
 *
 * @param string $option The configuration option name.
 * @return void
 */
if ( ! in_array('phpcore_ini_restore', $disable_functions) ) {
    function phpcore_ini_restore(string $option): void
    {
        \PHPCore\Config::restore($option);
    }
}

/**
 * Sets the value of a configuration option
 *
 * Sets the value of the given configuration option. The configuration option
 * will keep this new value during the script's execution, and will be restored
 * at the script's ending.
 *
 * @param string $option The configuration option name to set.
 * @param mixed $value The new value for the option.
 * @return mixed Returns the old value on success, null on failure.
 */
if ( ! in_array('phpcore_ini_set', $disable_functions) ) {
    function phpcore_ini_set(string $option, mixed $value): mixed
    {
        return \PHPCore\Config::set($option, $value);
    }
}

// -----------------------------------------------------------------------------

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
if ( ! in_array('request_agent', $disable_functions) ) {
    function request_agent(?string $key = null): mixed
    {
        return \PHPCore\Request::getAgent($key);
    }
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
 * <code linenos="true" emphasize-lines="5-7,12-13">
 *
 * $_POST = [ 'num' => 123, 'text' => 'abc'];
 *
 * var_dump(request_body('text')); // 'abc'
 * var_dump(request_body('num')); // '123'
 * var_dump(request_body()); // [ 'text' => 'abc', 'num' => '123' ]
 *
 * $_SERVER['CONTENT_TYPE'] = 'text/json';
 * // php://input <= {"num":456, "text":"John"}
 *
 * var_dump(request_body('text')); // 'John'
 * var_dump(request_body('num', FILTER_VALIDATE_INT)); // 456
 * 
 * </code>
 *
 * @param mixed $key Key of the body to retrieve.
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
if ( ! in_array('request_body', $disable_functions) ) {
    function request_body(
        mixed $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        return \PHPCore\Request::getBody($key, $filter, $options);
    }
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
 * <code linenos="true" emphasize-lines="5,6-7">
 *
 * $_COOKIE = [ 'PaginationOffset' => 1, 'PaginationOrder' => 'asc' ]
 *
 * echo request_cookie('PaginationOrder'); // 'asc'
 * var_dump(request_cookie('PaginationOffset', FILTER_VALIDATE_INT)); // 1
 * var_dump(request_cookie('PaginationOrder', FILTER_VALIDATE_INT)); // 1
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
if ( ! in_array('request_cookie', $disable_functions) ) {
    function request_cookie(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        return \PHPCore\Request::getCookie($key, $filter, $options);
    }
}

/**
 * Get file from request
 *
 * Will return the file by a given **$key** from the files that were
 * uploaded via the HTTP POST method using the ``$_FILES`` superglobal
 * variable.
 *
 * @example Get file from request
 * <code linenos="true" emphasize-lines="14-18">
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
 * $file = request_file('file_upload');
 * var_dump($file->getContents()); // '{"name":"Test","value":123}'
 * var_dump($file->isTrueType()); // false
 * var_dump($file->error); // 9
 * var_dump($file->getErrorMessage()); // 'File was not uploaded via HTTP POST'
 *
 * </code>
 *
 * @param string $key The key of the file to retrieve.
 * @param int $flags Bitwise flags for this method
 * @return ?object RequestFile object or ``null`` if the **$key** does not
 *                 exist.
 */
if ( ! in_array('request_file', $disable_functions) ) {
    function request_file(string $key, int $flags = 0): ?object
    {
        return \PHPCore\Request::getFile($key, $filter, $options);
    }
}

/**
 * Get files from request
 *
 * Will return an array of files for a given **$key** that were uploaded via
 * the HTTP POST method using the ``$_FILES`` superglobal variable.
 *
 * @example Get files from request
 * <code linenos="true" emphasize-lines="32-35">
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
 * $files = request_files('file_upload');
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
if ( ! in_array('request_files', $disable_functions) ) {
    function request_files(string $key, int $flags = 0): ?array
    {
        return \PHPCore\Request::getFile($key, $filter, $options);
    }
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
 * <code linenos="true" emphasize-lines="9,12,15">
 *
 * phpcore_ini_set('request.default_format', 'text');
 * phpcore_ini_set('request.supported_formats', [ 'text', 'xml', 'json' ]);
 *
 * $_SERVER['REQUEST_URI'] = '/';
 * $_SERVER['CONTENT_TYPE'] = null;
 *
 * echo request_format(); // 'csv'
 *
 * $_SERVER['REQUEST_URI'] = '/resource.xml?query=test';
 * echo request_format(); // 'xml'
 *
 * $_SERVER['CONTENT_TYPE'] = '/application/json';
 * echo request_format(); // 'json'
 *
 * </code>
 *
 * @return ?string The format that was requested.
 */
if ( ! in_array('request_format', $disable_functions) ) {
    function request_format(): ?string
    {
        return \PHPCore\Request::getFormat();
    }
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
 * <code linenos="true" emphasize-lines="7-9">
 *
 * $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
 * $_SERVER['HTTP_CUSTOM_HEADER'] = '1';
 * $_SERVER['HTTP_X_CUSTOM_HEADER'] = '2';
 *
 * echo request_header('accept-encoding'); // 'gzip, deflate'
 * echo request_header('custom-header'); // '1'
 * var_dump(request_header('x-custom-header', FILTER_VALIDATE_INT)); // 1
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
if ( ! in_array('request_header', $disable_functions) ) {
    function request_header(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        return \PHPCore\Request::getHttpHeader($key, $filter, $options);
    }
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
 * <code linenos="true" emphasize-lines="6,11-12">
 *
 * $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
 * $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.2';
 *
 * echo request_ipaddress(); // '10.0.0.1'
 *
 * $_SERVER['REMOTE_ADDR'] = '10.0.1';
 * $_SERVER['HTTP_X_FORWARDED_FOR'] = null;
 *
 * var_dump(request_ipaddress()); // '10.0.1'
 * var_dump(request_ipaddress(true)); // false
 *
 * </code>
 *
 * @param bool $check_valid Check using ``FILTER_VALIDATE_IP`` filter.
 * @return ?string IP address makeing request.
 */
if ( ! in_array('request_ipaddress', $disable_functions) ) {
    function request_ipaddress($check_valid = true): mixed
    {
        return \PHPCore\Request::getIpAddress($check_valid);
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
 * <code linenos="true" emphasize-lines="5-7">
 *
 * $_SERVER['REQUEST_URI'] = '/index.php?text=abc&num=12345';
 *
 * var_dump(request_param()); // [ 'text' => 'abc', 'num' => '12345' ]
 * var_dump(request_param('text')); // 'abc'
 * var_dump(request_param('num', FILTER_VALIDATE_INT)); // 12345
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
if ( ! in_array('request_param', $disable_functions) ) {
    function request_param(
        ?string $key = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        return \PHPCore\Request::getParameter($key, $filter, $options);
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
 * <code linenos="true" emphasize-lines="5-9,12">
 *
 * $_SERVER['REQUEST_URI'] = '/sections/articles/12345.html';
 *
 * var_dump(request_segment()); // [ 'sections', 'articles', '12345' ]
 * var_dump(request_segment(0)); // 'sections'
 * var_dump(request_segment(4)); // null
 * var_dump(request_segment(2, FILTER_VALIDATE_INT)); // 12345
 * var_dump(request_segment(1, FILTER_VALIDATE_INT)); // false
 *
 * phpcore_ini_set('request.segment_offset', 1);
 * var_dump(request_segment(0)); // 'articles'
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
if ( ! in_array('request_segment', $disable_functions) ) {
    function request_segment(
        ?int $pos = null,
        int $filter = FILTER_DEFAULT,
        array|int $options = 0
    ): mixed {
        return \PHPCore\Request::getSegment($pos, $filter, $options);
    }
}

// -----------------------------------------------------------------------------

// TODO: RBF
function ________________CORE_FUNCTIONS________________(){}

/**
 * Parse a csv file
 *
 * Convert CSV file to a PHP variable.
 *
 * @param string $path The file path.
 * @param bool $first_row_keys First row contains keys.
 * @return mixed Data array for csv.
 */
if ( ! in_array('csv_parse_file', $disable_functions) ) {
    function csv_parse_file(string $path, bool $first_row_keys = true): array
    {
        $data = [];
        $keys = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $row_cnt = 0;
            while (($raw_row = fgetcsv($handle)) !== false) {
                $row_cnt++;
                if ($first_row_keys && $row_cnt === 1) {
                    $keys = $raw_row;
                    continue;
                } elseif ($first_row_keys === false) {
                    $data[] = $raw_row;
                    continue;
                }
                $row = [];
                foreach ($keys as $i => $key) {
                    $row[$key] = $raw_row[$i];
                }
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }
}

// CLEAN CODE BELOW >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// TODO: RBF
function ________________CLEAN_CODE_LINE________________(){}

// TODO: document
/*
if ( ! in_array('array_every', $disable_functions) ) {
    function array_every(array $arr, callable $func): bool
    {
        foreach ($arr as $item) {
            if ( ! call_user_func($func, $item)) {
                return false;
            }
        }
        return true;  
    }
}
*/

/**
 * Flatten array
 *
 * Returns a flatten or single dimensional array.
 *
 * @param array $arr Array
 * @param array $flattened Items that are already flattened
 * @return array Returns flatten array
 *
if ( ! in_array('array_flatten', $disable_functions) ) {
    function array_flatten(array $arr, array $flattened = []): array
    {
        foreach ($arr as $item) {
            if (is_array ($item)) {
                $flattened = array_flatten($item, $flattened);
            } elseif (is_object($item)) {
                $flattened = array_flatten((array)$item, $flattened);
            } else {
                $flattened[] = $item;
            }
        }
        return $flattened;
    }
}
*/

// TODO: document
/*
if ( ! in_array('array_some', $disable_functions) ) {
    function array_some(array $arr, callable $func): bool
    {
        foreach ($arr as $item) {
            if (call_user_func($func, $item)) {
                return true;
            }
        }
        return false;  
    }
}
*/

/**
 * Get PHPCore Information
 *
 * @todo: Build HTML pretty output
 *
 * @return string List or HTML formated PHPCore information.
 *
if ( ! in_array('coreinfo', $disable_functions) ) {
    function coreinfo(): void
    {
        $output = '';
        $version = CORE_VERSION;
        $eol = PHP_EOL;
        $format = $GLOBALS['_CORE']['FORMAT'];
        switch ($format) {
            default:
                trigger_error(
                    "coreinfo() does not support the '$format' format.",
                    E_USER_ERROR
                );
            case 'text':
                $output .= str_color('PHPCore', 'light_blue') . ' ' . str_color($version, 'cyan') . $eol;
                foreach ($GLOBALS['_CORE_INI'] as $section=>$directives) {
                    $output .= PHP_EOL . str_color(str_style($section, 'underline'), 'brown') . $eol;
                    foreach ($directives as $directive=>$value) {
                        $output .= str_color($directive, 'green')." => $value" . $eol;
                    }
                }
                $output .= PHP_EOL;
                $output .= str_color(str_style('$_CORE', 'underline'), 'brown') . $eol;
                foreach ($GLOBALS['_CORE'] as $name=>$value) {
                    $output .= "\$_CORE['".str_color($name, 'green')."'] => $value" . $eol;
                }
                $output .= PHP_EOL;
                echo $output;
                exit;
            break;
            case 'html':
                $output .= "<!DOCTYPE html><html lang=\en\"><head><title>PHPCore $version</title><meta charset=\"utf-8\">";
                $output .= "<style>
body {background-color: #fff; color: #222; font-family: sans-serif;}
pre {margin: 0; font-family: monospace;}
a:link {color: #009; text-decoration: none; background-color: #fff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
.center {text-align: center;}
.center table {margin: 1em auto; text-align: left;}
.center th {text-align: center !important;}
td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
th {position: sticky; top: 0; background: inherit;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #cef; width: 300px; font-weight: bold;}
.h {background-color: #9bc; font-weight: bold;}
.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
.v i {color: #999;}
span {float: right;}
hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
</style>";
                $output .= "</head><body><div class=\"center\">";
                $output .= "<table><tr class=\"h\"><td><h1 class=\"p\">PHPCore $version ";
                $output .= "<span>PHP Version ".phpversion()."</span></h1></td></tr></table>";
                foreach ($GLOBALS['_CORE_INI'] as $section=>$directives) {
                    $output .= "<hr><h1>$section</h1><table>";
                    $output .= "<tr class=\"h\"><th>Directive</th><th>Value</th></tr>";
                    foreach ($directives as $directive=>$value) {
                        $output .= "<tr><td class=\"e\">$directive</td><td class=\"v\">$value</td></tr>";
                    }
                    $output .= "</table>";
                }
                $output .= "<hr><h1>Core Variables</h1>";
                $output .= "<table>";
                $output .= "<tr class=\"h\"><th>Directive</th><th>Value</th></tr>";
                foreach ($GLOBALS['_CORE'] as $name=>$value) {
                    $output .= "<tr><td class=\"e\">$name</td><td class=\"v\">$value</td></tr>";
                }
                $output .= "</table></div></body></html>";
                PHPCore\Response::send($output);
                exit;
            break;
            case 'json':
            case 'xml':
                $data = ['PHPCoreVersion'=>$version];
                $data['configuration'] = $GLOBALS['_CORE_INI'];
                $data['variables'] = $GLOBALS['_CORE'];
                if ($format === 'json') {
                    $output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } elseif ($format === 'xml') {
                    $output = xml_encode($data, XML_ENCODE_PRETTY_PRINT);
                }
                PHPCore\Response::send($output);
                exit;
            break;
        }
    }
}
*/

/**
 * Get database class instance
 *
 * Returns the current database instance. If the database has not been started yet it will be started
 * before the instance is returned.
 *
 * @param string $name Name of instance
 * @return object Database
 *
if ( ! in_array('database', $disable_functions) ) {
    function &database(?string $name = null): object
    {
        return \PHPCore\Database::getInstance($name);
    }
}
*/

/**
 * Delete cookie
 *
 * Defines a cookie to be sent along with the rest of the HTTP headers with an expiration time in
 * the past therefor telling the browser the cookie has expired.
 *
 * @param string $name The name of the cookie.
 * @param string $path The path on the server in which the cookie will be delete for. The default
 *                     value is the current directory that the cookie is being deleted in.
 * @param string $domain The (sub)domain that the cookie will be deleted for.
 *
if ( ! in_array('delcookie', $disable_functions) ) {
    function delcookie(string $name, string $path = '', string $domain = '')
    {
        setcookie($name, '', -1, $path, $domain);
    }
}
*/

// TODO: document
/*
if ( ! in_array('param', $disable_functions) ) {
    function param(string $key): mixed
    {
        return match($key) {
            '@user_id' => user()->UserId,
            '@format'  => request_format(),
            '@method'  => strtolower($_SERVER['REQUEST_METHOD']),
            default    => null,
        };
    }
}
*/

/**
* Parse Docblock
*
* @param string $doc_block phpDocumentor Comment Block
* @return object|false Parsed docBlock
*/
function parse_docblock(string $doc_block): object|false
{
    $details = [
        'title' => null,
        'description' => '',
    ];

    $line_buffer = null;
    $completed_description = false;
    $lines = explode("\n", trim($doc_block));

    if (count($lines) < 3) {
        trigger_error('doc block is not valid');
    }

    // Skip the first and last lines
    unset($lines[count($lines)-1]);
    unset($lines[0]);

    // Initial Cleaning remove white space and starting "*"
    foreach($lines as $index=>$row) {
        $lines[$index] = trim(substr(trim($row), 1));
    }

    // Parse
    $tags = [];
    $row_honor_eol = false;
    foreach($lines as $index=>$row) {

        $next_line = $lines[$index+1] ?? null;

        if (strpos($row, '@') === 0) {
            $completed_description = true;
        }

        // Skip empty row
        if (empty($row) && ! $completed_description) {
            if (isset($next_line) && strpos($next_line, '@') !== 0 && $details['description'] != '') {
                $details['description'] .= "\n\n";
            }
            continue;
        }

        // Title
        if ( ! isset($details['title'])) {
            if (strpos($row, '@') === 0) {
                $details['title'] = '';
            } else {
                $details['title'] = $row;
                continue;
            }
        }

        // Description
        if ( ! $completed_description) {
            if ($details['description'] == '') {
                $details['description'] = $row;
            } elseif (substr($details['description'], -1) === "\n") {
                $details['description'] .= $row;
            } else {
                $details['description'] .= " $row";
            }
            continue;
        }

        if (strpos($row, '<code') === 0) {
            $row_honor_eol = true;
        }

        // Start of new tag
        if (strpos($row, '@') === 0) {
            $tags[] = substr($row, 1);
            $row_honor_eol = false;
        } elseif(isset($tags[count($tags)-1])) {
            if ($row_honor_eol) {
                $tags[count($tags)-1] .= "\n$row";
            } else {
                $tags[count($tags)-1] .= " $row";
            }
        }
    }

    foreach ($tags as $tag) {

        $parts = explode(' ', preg_replace('!\s+!', ' ', $tag));

        switch ($parts[0]) {
            // DEFAULT (single)
            default:
                $details[$parts[0]] = trim(substr($tag, strlen($parts[0])));
            break;

            // DEFAULT (multiple)
            case 'note':
            case 'warning':
            case 'seealso':
                if ( ! isset($details[$parts[0]])) {
                    $details[$parts[0]] = [];
                }
                $details[$parts[0]][] = trim(substr($tag, strlen($parts[0])));
            break;

            // BOOLEAN
            case 'ignore':
                $details[$parts[0]] = true;
            break;

            // EXAMPLE
            case 'example':
                $code = trim(substr($tag, strlen($parts[0])));
                preg_match('/(.+\n)(\<.+\>)((.|\n)+)(\<\/.+\>)/', $code, $matches);
                $code = (object)[
                    'caption' => $matches[1],
                    'code' => $matches[3],
                ];
                preg_match_all('/([\w\-]+)\=\"(.+?)\"/', trim(substr($matches[2], 5, -1)), $matches);
                $attributes = [];
                foreach ($matches[1] as $index=>$match) {
                    $attributes[$match] = $matches[2][$index];
                }
                if ( ! empty($attributes)) {
                    $code->attributes = $attributes;
                }
                $details[$parts[0]] = $code;
            break;

            // PARAM
            case 'param':
                if (count($parts) < 4) {
                    trigger_error('@param tag is not structured correctly "'.implode(' ', $parts).'"');
                }
                if ( ! isset($details['params'])) {
                    $details['params'] = [];
                }
                $details['params'][] = (object)[
                    'type' => $parts[1],
                    'name' => $parts[2],
                    'description' => trim(implode(' ', array_slice($parts, 3))),
                ];
            break;

            // REFENCE
            case 'refence':
                if ( ! isset($details['refences'])) {
                    $details['refences'] = [];
                }
                $parts = explode(':', implode(' ', array_slice($parts, 1)));
                if (count($parts) < 2) {
                    trigger_error('@refence tag is not structured correctly "'.implode(':', $parts).'"');
                }
                $details['refences'][] = (object)[
                    'description' => $parts[0],
                    'link' => trim(implode(':', array_slice($parts, 1))),
                ];
            break;

            // RETURN
            case 'return':
                if (count($parts) < 2) {
                    trigger_error('@return tag is not structured correctly "'.implode(' ', $parts).'"');
                }
                $details['return'] = (object)[
                    'type' => $parts[1],
                    'description' => trim(implode(' ', array_slice($parts, 2))),
                ];
            break;

            // THROWS
            case 'throws':
                if (count($parts) < 3) {
                    trigger_error('@throw tag is not structured correctly "'.implode(' ', $parts).'"');
                }
                if ( ! isset($details['throws'])) {
                    $details['throws'] = [];
                }
                $details['throws'][] = (object)[
                    'type' => $parts[1],
                    'description' => trim(implode(' ', array_slice($parts, 2))),
                ];
            break;
        }
    }

    return (object)$details;
}

/**
 * Parse DSN string
 *
 * This function will parse a given Data Source Name (DSN) string and return an
 * associated array of its contents.
 *
 * @example
 * ```php
 *
 * $dsn_str = 'mysql:host=localhost;dbname=my_database;charset=utf8mb4';
 * $dsn_arr = parse_dsn($dsn_str);
 * echo $dsn_arr['driver'];  // mysql
 * echo $dsn_arr['host'];    // localhost
 * echo $dsn_arr['dbname'];  // my_database
 * echo $dsn_arr['charset']; // utf8mb4
 *
 * ```
 *
 * @see https://www.php.net/manual/en/pdo.drivers.php
 *
 * @param string $dsn Data Source Name (DSN) string to parse.
 * @return array Returns DSN elements as associated array.
 *
if ( ! in_array('parse_dsn', $disable_functions) ) {
    function parse_dsn(string $dsn): array
    {
        if (strpos($dsn, ':') === false) {
            throw new InvalidArgumentException(
                'parse_dsn function only accepts valid dsn strings'
            );
        }
        $dsn_parts = explode(':', $dsn);
        $driver = $dsn_parts[0];
        $params = $dsn_parts[1] ?? '';
        $output['driver'] = $driver;
        foreach(explode(';', $params) as $item) {
            if (empty($item)) {
                continue;
            }
            switch ($driver) {
                case 'sqlite':
                    $output['path'] = $item;
                    preg_match('/(\w+)(\.\w+)?((?!.*(\w+)(?!\.\w+)+))/', $item, $matches);
                    $output['dbname'] = $matches[1];
                    return $output;
            }
            list($name, $value) = explode('=', $item);
            $output[$name] = match ($name) {
                'port'   => intval($value),
                'weight' => intval($value),
                default  => $value
            };
        }
        return $output;
    }
}
*/

// TODO: document
/*
if ( ! in_array('response_add', $disable_functions) ) {
    function response_add(string|array $key, mixed $data = null): void
    {
        \PHPCore\Response::add($key, $data);
    }
}
*/

// TODO: document
/*
if ( ! in_array('response_error', $disable_functions) ) {
    function response_error(float $code, array $params = [], int $flags = 0): void
    {
        \PHPCore\Response::error($code, $params, $flags);
    }
}
*/

// TODO: document
/*
if ( ! in_array('response_send', $disable_functions) ) {
    function response_send(mixed $data = null, ?int $statusCode = null): void
    {
        \PHPCore\Response::send($data, $statusCode);
    }
}
*/

/**
 * Get session class instance
 *
 * Returns the current session instance. If the session has not been started yet it will be started
 * before the instance is returned.
 *
 * @return object Session
 *
if ( ! in_array('session', $disable_functions) ) {
    function &session(): object
    {
        return \PHPCore\Session::getInstance();
    }
}
*/

/**
 * Destroy all sessions
 *
 * Destroys **ALL** sessions if the save handlers supports this method.
 *
 * @return boolean Returns true on success or false on failure.
 * @throws Exception If save handler does not support this method.
 *
if ( ! in_array('session_destroy_all', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_destroy_all(): bool
    {
        return \PHPCore\Session::getInstance()->destroyAll();
    }
}
*/

/**
 * Get session flash data item
 *
 * This method will return the flash data item that matches the provided key. If a key is not
 * provided the entire flash data array will be returned.
 *
 * @param ?string $key The key of the flash data item to retrieve
 * @return mixed Returns the flash data item
 *
if ( ! in_array('session_flash_get', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_get(?string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->flashGet($key);
    }
}
*/

/**
 * Keep session flash data item
 *
 * This method will keep a session flash data item for the next session.
 *
 * @param string $key The key of the flash data item to keep
 * @return boolean Return true on success and false if not found
 *
if ( ! in_array('session_flash_keep', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_keep(string $key): bool
    {
        return \PHPCore\Session::getInstance()->flashKeep($key);
    }
}
*/

/**
 * Set session flash data item
 *
 * This method will set a session flash data item to be used for the next session.
 *
 * @param string $key The key of the flash data item
 * @param mixed $value The value of the flash data item
 * @return void
 *
if ( ! in_array('session_flash_set', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_set(string $key, mixed $value): void
    {
        \PHPCore\Session::getInstance()->flashSet($key, $value);
    }
}
*/

/**
 * Get session data item
 *
 * This method is used to retrieve a session data item.
 *
 * @param ?string $key Key of session data item to retrieve
 * @return mixed Data item from session data
 *
if ( ! in_array('session_get', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_get(?string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->get($key);
    }
}
*/

/**
 * Returns all the session metadata
 *
 * This method will get metadata with a provided key. If no key is passed the
 * entire metadata array will be returned.
 *
 * @param string $key Metadata Key
 * @return mixed Session Metadata
 *
if ( ! in_array('session_get_metadata', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_get_metadata(?string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->getMetadata($key);
    }
}
*/

/**
 * Grant session access
 *
 * // TODO: move to User Class
 *
 * This method grants session access via adding it the the ``acl_groups``
 * array in the sessions metadata.
 *
 * @param string|array $groups ACL group or array of ACL groups to be granted
 * @return void
 *
if ( ! in_array('session_grant', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_grant(string|array $groups): void
    {
        \PHPCore\Session::getInstance()->grant($groups);
    }
}
*/

/**
 * Revoke session access
 *
 * // TODO: move to User Class
 *
 * This method removes session access via removing from ``acl_groups`` array in
 * the sessions metadata.
 *
 * @param string|array $groups ACL group or array of ACL groups to be revoked
 * @return void
 *
if ( ! in_array('session_revoke', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_revoke(string|array $groups): void
    {
        \PHPCore\Session::getInstance()->revoke($groups);
    }
}
*/

/**
 * Set session data item
 *
 * This method is used to store a session data item. If the optional ``$ttl``
 * is passed the data item will also be given an expiration.
 *
 * @param string $key Key of session data item to set.
 * @param mixed $value Value of session data item to set.
 * @param integer $ttl Time To Live for this data item.
 *
if ( ! in_array('session_set', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_set(string $key, mixed $value, ?int $ttl = null): void
    {
        \PHPCore\Session::getInstance()->set($key, $value, $ttl);
    }
}
*/

/**
 * Set the session metadata
 *
 * This method is used to store a session metadata data item.
 *
 * @param string $key Key of session data item to set.
 * @param mixed $value Value of session data item to set.
 *
if ( ! in_array('session_set_metadata', $disable_functions) and ! in_array('Session', $disable_classes)) {
    function session_set_metadata(string $key, mixed $value): void
    {
        \PHPCore\Session::getInstance()->setMetadata($key, $value);
    }
}
*/

/**
 * Returns terminal colored string
 *
 * This is done by escape character so we can actually define a output color. This is done with \033 (\e).
 *
 * @param string $string         String to be colorized
 * @param string $str_color_name String color name
 * @param string $bkg_color_name Background color name
 * @return string
 */
if ( ! in_array('str_color', $disable_functions) ) {
    function str_color(string $string, string $str_color_name, string $bkg_color_name = 'black'): string
    {
        switch ($str_color_name) {
            case 'black':         $text_color = '0;30'; break;
            case 'dark_grey':     $text_color = '1;30'; break;
            case 'red':           $text_color = '0;31'; break;
            case 'light_red':     $text_color = '1;31'; break;
            case 'green':         $text_color = '0;32'; break;
            case 'light_green':   $text_color = '1;32'; break;
            case 'gold':          $text_color = '0;33'; break;
            case 'brown':         $text_color = '0;33'; break;
            case 'yellow':        $text_color = '1;33'; break;
            case 'blue':          $text_color = '0;34'; break;
            case 'light_blue':    $text_color = '1;34'; break;
            case 'magenta':       $text_color = '0;35'; break;
            case 'light_magenta': $text_color = '1;35'; break;
            case 'cyan':          $text_color = '0;36'; break;
            case 'light_cyan':    $text_color = '1;36'; break;
            case 'light_grey':    $text_color = '0;37'; break;
            case 'white':         $text_color = '1;37'; break;
            default:
                trigger_error(
                    "Unknown string color '$str_color_name' used for str_color()",
                    E_USER_ERROR
                );
        }
        switch ($bkg_color_name) {
            case 'black':   $bkgd_color = '40'; break;
            case 'red':     $bkgd_color = '41'; break;
            case 'green':   $bkgd_color = '42'; break;
            case 'yellow':  $bkgd_color = '43'; break;
            case 'blue':    $bkgd_color = '44'; break;
            case 'magenta': $bkgd_color = '45'; break;
            case 'cyan':    $bkgd_color = '46'; break;
            case 'white':   $bkgd_color = '47'; break;
            default:
                trigger_error(
                    "Unknown background color '$bkg_color_name' used for str_color()",
                    E_USER_ERROR
                );
        }
        return "\e[{$text_color};{$bkgd_color}m{$string}\e[0m";
    }
}

/**
 * Returns terminal styled string
 *
 * This is done by escape character so we can actually define a output color. This is done with \033 (\e).
 *
 * @param string $string     String to be styled
 * @param string $style_name Style name
 * @return void
 */
if ( ! in_array('str_style', $disable_functions) ) {
    function str_style(string $string, string $style_name): string
    {
        switch ($style_name) {
            case 'bold':
            case 'bright':        return "\e[1m{$string}\e[0m"; break;
            case 'dim':           return "\e[2m{$string}\e[0m"; break;
            case 'italic':        return "\e[3m{$string}\e[0m"; break;
            case 'underline':     return "\e[4m{$string}\e[0m"; break;
            //case 'blink':         return "\e[5m{$string}\e[0m"; break;
            //case 'unknown':       return "\e[6m{$string}\e[0m"; break;
            case 'reverse':       return "\e[7m{$string}\e[0m"; break;
            case 'hidden':        return "\e[8m{$string}\e[0m"; break;
            case 'strike':
            case 'strikethrough': return "\e[9m{$string}\e[0m"; break;
            default:
                trigger_error(
                    "Unknown string style `$style_name` used for str_style()",
                    E_USER_ERROR
                );
        }
    }
}

/**
 * Is string casing
 *
 * This method will check the provided string has proper casing.
 *
 * @todo offer number support?
 *
 * Supported Casings are:
 *   - StudlyCaps
 *   - snake_case
 *   - camelCase
 *   - kebab-case
 *   - lowercase
 *   - UPPER_CASE
 *   - UPPERCASE
 *
 * @param bool
 * @return bool
 */
if ( ! in_array('is_casing', $disable_functions) ) {
    function is_casing(string $string, string $casing): bool
    {
        switch ($casing) {
            case 'StudlyCaps':
                return (preg_match("/^([A-Z][a-z]+)+$/", $string) != false);
            break;
            case 'snake_case':
                return (preg_match("/^[a-z_]+$/", $string) != false);
            break;
            case 'camelCase':
                return (preg_match("/^([a-z]+[A-Z]?)+$/", $string) != false);
            break;
            case 'kebab-case':
                return (preg_match("/^[a-z\-]+$/", $string) != false);
            break;
            case 'lowercase':
                return (preg_match("/^[a-z]+$/", $string) != false);
            break;
            case 'UPPER_CASE':
                return (preg_match("/^[A-Z_]+$/", $string) != false);
                return false;
            break;
            case 'UPPERCASE':
                return (preg_match("/^[A-Z]+$/", $string) != false);
            break;
            default:
                trigger_error(
                    "Unknown casing `$casing` used for is_casing()",
                    E_USER_ERROR
                );
        }
    }
}

/**
 * Time to array
 *
 * Takes a provide time and returns an array of time units.
 *
 * @param integer $time Time
 * @return array
 *
if ( ! in_array('timetoarray', $disable_functions) ) {
    function timetoarray(int $time): array
    {
        return [
          'secs' => $time % 60,
          'mins' => floor( ($time % 3600) / 60),
          'hrs'  => floor( ($time % 86400) / 3600),
          'days' => floor( ($time % 2592000) / 86400),
        ];
    }
}
*/

/**
 * Get user class instance
 *
 * Returns the current user instance.
 *
 * @return object Session
 *
if ( ! in_array('user', $disable_functions) and ! in_array('User', $disable_classes)) {
    function user(): object
    {
        static $user;

        if (empty($user)) {
            $user_id = \PHPCore\Session::get('user_id');
            $user = new \PHPCore\User($user_id);
        }

        return $user;
    }
}
*/

/**
 * Check if has role
 *
 * This method checks if user has access via checking if in the ``acl_groups``
 * array in the sessions metadata.
 *
 * @param string|array $groups ACL group string or array of ACL groups to check
 *                             access for
 * @param integer $flags Bitwise flags for this method
 * @flag User::HAS_ACCESS_ANY Has Access check true on ANY match
 * @flag User::HAS_ACCESS_ALL Has Access check true if ALL match
 * @return boolean If has session access
 *
if ( ! in_array('user_has_role', $disable_functions) and ! in_array('User', $disable_classes)) {
    function user_has_role(string|array $groups, int $flags = 0): bool
    {
        return user()->hasAccess($groups, $flags);
    }
}
*/

// TODO: document
/*
if ( ! in_array('user_add_role', $disable_functions) and ! in_array('User', $disable_classes)) {
    function user_add_role(string|array $roles, int $flags = 0): void
    {
        user()->addRole($roles, $flags);
    }
}
*/

// TODO: document
/*
if ( ! in_array('user_remove_role', $disable_functions) and ! in_array('User', $disable_classes)) {
    function user_remove_role(string|array $roles): void
    {
        user()->removeRole($roles);
    }
}
*/

/**
 * Returns the XML representation of a array
 *
 * @param array $array Array to be encoded as XML
 * @return string Returns a string containing the XML representation of the supplied array.
 *
if ( ! in_array('xml_encode', $disable_functions) ) {
    define('XML_ENCODE_AS_XML_OBJ', 1);
    define('XML_ENCODE_PRETTY_PRINT', 2);
    function xml_encode(mixed $array, int $flags = 0)
    {
        static $sub_func = null;
        if (empty($sub_func)) {
            $sub_func = function($arr, $str, $xml) use(&$sub_func) {
                foreach ($arr as $key=>$val) {
                    if(is_numeric($key)) {
                        $key = 'item';
                    }
                    if (is_array($val)) {
                        $sub_func($val, $key, $xml->addChild($key));
                    } else {
                        $xml->addChild($key, strval($val));
                    }
                }
                return $xml;
            };
        }
        $xml = $sub_func($array, '<root/>', new SimpleXMLElement('<root/>'));
        if ($flags & XML_ENCODE_AS_XML_OBJ) {
            return $xml;
        }
        if ($flags & XML_ENCODE_PRETTY_PRINT) {
            $dom = dom_import_simplexml($xml)->ownerDocument;
            $dom->formatOutput = true;
            return $dom->saveXML();
        } else {
            return $xml->asXML();
        }
    }
}
*/

unset($disable_functions);
unset($disable_classes);

// EOF /////////////////////////////////////////////////////////////////////////
