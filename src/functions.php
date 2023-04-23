<?php declare(strict_types=1);
/**
 * PHPCore - Functions
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2023, PHPCore
 */

// -------------------------------------------------------------------------------------------------

$disabled_functions = $disable_classes = [];
if (isset($GLOBALS['_CORE']['DISABLE_FUNCTIONS'])) {
    $disabled_functions = explode(',', $GLOBALS['_CORE']['DISABLE_FUNCTIONS']);
}
if (isset($GLOBALS['_CORE']['DISABLE_CLASSES'])) {
    $disable_classes = explode(',', $GLOBALS['_CORE']['DISABLE_CLASSES']);
}

// TODO: document
if ( ! in_array('array_every', $disabled_functions) ) {
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

/**
 * Find in array
 *
 * Returns the first item of on array based on a callable function.
 *
 * @param array $arr Array to search.
 * @param callable $func Function to be called to perform check.
 * @return mixed|null Returns the first array item that returns true for the
 *                    callable function.
 *                    Returns null if not found.
 */
if ( ! in_array('array_find', $disabled_functions) ) {
    function array_find($arr, $func): mixed
    {
        foreach ($arr as $item) {
            if (call_user_func($func, $item)) {
                return $item;
            }
        }
        return null;
    }
}


/**
 * Flatten array
 *
 * Returns a flatten or single dimensional array.
 *
 * @param array $arr Array
 * @param array $flattened Items that are already flattened
 * @return array Returns flatten array
 */
if ( ! in_array('array_flatten', $disabled_functions) ) {
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

// TODO: document
if ( ! in_array('array_some', $disabled_functions) ) {
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

/**
 * Gets the value of a PHPCore configuration directive
 *
 * Returns the value of the PHPCore configuration directive on success. If
 * section is not passed 'the [PHPCore] section will be used.
 *
 * @param string $directive The configuration directive name.
 * @param string $section The configuration section name.
 * @return string|false Returns the value of the configuration option as a
 *                      string on success, or an empty string for null values.
 *                      Returns false if the configuration option doesn't exist.
 */
if ( ! in_array('core_ini_get', $disabled_functions) ) {
    function core_ini_get(string $directive, string $section = 'PHPCore'): string|false
    {
        //print_r($GLOBALS['_CORE_INI'][$section]);
        return $GLOBALS['_CORE_INI'][$section][$directive] ?? false;
    }
}

/**
 * Gets all configuration options
 *
 * Returns all the registered configuration options.
 *
 * @param string $section An optional section name. If not empty, the function
 *                        returns only options specific for that section.
 * @return array Returns an associative array with directive name as the array
 *               key. Returns false and raises an E_WARNING level error if the
 *               section doesn't exist.
 */
if ( ! in_array('core_ini_get_all', $disabled_functions) ) {
    function core_ini_get_all(?string $section = null, ?string $sub_section = null): array|false
    {
        if (empty($section)) {
            return $GLOBALS['_CORE_INI'] ?? false;
        } elseif(empty($sub_section)) {
            return $GLOBALS['_CORE_INI'][$section] ?? false;
        } else {
            $sub_directives = [];
            if (empty($GLOBALS['_CORE_INI'][$section]) === false) {
                foreach ($GLOBALS['_CORE_INI'][$section] as $directive=>$value) {
                    if (preg_match("/($sub_section)\.(\w*)/", $directive, $matches)) {
                        $sub_directives[$matches[2]] = $value;
                    }
                }
            }
            return empty($sub_directives) ? false : $sub_directives;
        }
    }
}

/**
 * Sets the value of a configuration directive.
 *
 * Sets the value of the given PHPCore configuration directive. The configuration
 * directive will keep this new value during the script's execution, and will be
 * restored at the script's ending. This is similar to PHP ini_set() function.
 *
 * @param string $directive The configuration directive name.
 * @param string $section The configuration section name.
 * @param string $value The new value for the option.
 * @return string|false Returns the old value on success, false on failure.
 */
if ( ! in_array('core_ini_set', $disabled_functions) ) {
    function core_ini_set(string $directive, mixed $value, string $section = 'PHPCore'): string|false
    {
        $oldValue =  $GLOBALS['_CORE_INI'][$section][$directive] ?? '';
        if (isset($GLOBALS['_CORE_INI'][$section])) {
            $GLOBALS['_CORE_INI'][$section] = [];
        }
        $GLOBALS['_CORE_INI'][$section][$directive] = $value;
        return $oldValue;
    }
}

/**
 * Get PHPCore Information
 *
 * @todo: Build HTML pretty output
 *
 * @return string List or HTML formated PHPCore information.
 */
if ( ! in_array('coreinfo', $disabled_functions) ) {
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

/**
 * Get database class instance
 *
 * Returns the current database instance. If the database has not been started yet it will be started
 * before the instance is returned.
 *
 * @param string $name Name of instance
 * @return object Database
 */
if ( ! in_array('database', $disabled_functions) ) {
    function &database(?string $name = null): object
    {
        return \PHPCore\Database::getInstance($name);
    }
}

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
 */
if ( ! in_array('delcookie', $disabled_functions) ) {
    function delcookie(string $name, string $path = '', string $domain = '')
    {
        setcookie($name, '', -1, $path, $domain);
    }
}

// TODO: document
if ( ! in_array('param', $disabled_functions) ) {
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
            $details['title'] = $row;
            continue;
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
                    'varable' => $parts[2],
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
 */
if ( ! in_array('parse_dsn', $disabled_functions) ) {
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

/**
 * Get request agent capabilities
 *
 * Attempts to determine the capabilities of the user's browser, by looking
 * up the browser's information in the browscap.ini file. Then returns the
 * capability by the given **$key**.
 *
 * If ``$key ``is not passed the entire capabilities object will be returned.
 *
 * Returns **NULL** if get_browser() fails or requested capability is unknown.
 *
 * @param string $key The key of the capability data item to retrieve
 * @return mixed The request capability or the entire capability object
 */
if ( ! in_array('request_agent', $disabled_functions) ) {
    function request_agent(?string $key = null): mixed
    {
        return \PHPCore\Request::agent($key);
    }
}

/**
 * Get data from request body
 *
 * Will parsed the request body based on the format, then return data from the
 * parsed body by a given **$key** for data passed via the HTTP POST method. The
 * option **$filter** and **$options** parameters may be given to invoke
 * ``filter_var()`` before the value is returned.
 *
 * If **$key** is not passed the request body be returned and the **$filter**
 * and **$options** will be ignored.
 *
 * Supported Filters & Options:
 * https://www.php.net/manual/en/filter.filters.php
 *
 * @param string $key The key of the body's data to retrieve
 * @param integer $filter The ID of the filter to apply
 * @param array|int $options Associative array of options or bitwise disjunction
 *                           of flags
 * @return mixed The requested data item
 */
if ( ! in_array('request_body', $disabled_functions) ) {
    function request_body(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
    {
        return \PHPCore\Request::agent($key);
    }
}

/**
 * Get data from HTTP cookie
 *
 * Will return data from cookie by a given **$key** for data passed via HTTP
 * Cookies. The option **$filter** and **$options** parameters may be given to
 * invoke ``filter_var()`` before the value is returned.
 *
 * Supported Filters & Options:
 * https://www.php.net/manual/en/filter.filters.php
 *
 * @param string $key The key of the cookie to retrieve
 * @param integer $filter The ID of the filter to apply
 * @param array|int $options Associative array of options or bitwise
 *                           disjunction of flags
 * @return mixed The requested data item
 */
if ( ! in_array('request_cookie', $disabled_functions) ) {
    function request_cookie(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        return \PHPCore\Request::cookie($key, $filter, $options);
    }
}

/**
 * Get file from request
 *
 * Will return the file by a given **$key** for the files that was uploaded via
 * the HTTP POST method using the ``$_FILES`` superglobal variable.
 *
 * @param string $key The key of the file to retrieve
 * @return object|null RequestFile object
 */
if ( ! in_array('request_file', $disabled_functions) ) {
    function request_file(string $key): object|null
    {
        return \PHPCore\Request::file($key);
    }
}

/**
 * Get files from request
 *
 * Will return an array of files for a given **$key** that were uploaded via the
 * HTTP POST method using the ``$_FILES`` superglobal variable.
 *
 * @param string $key The key of the array of files to retrieve
 * @return array Array of RequestFile objects
 */
if ( ! in_array('request_files', $disabled_functions) ) {
    function request_files(string $key): array
    {
        return \PHPCore\Request::files($key);
    }
}

/**
 * Get the requested format
 *
 * This method will return the request format by first looking at the
 * requested CONTENT_TYPE, if unknown then it will attempt to decipher using
 * the REQUEST_URI extention. If format cannot be determine then the
 * default_format set in the INI will be used.
 *
 * @return string Format extention
 */
if ( ! in_array('request_format', $disabled_functions) ) {
    function request_format(): string
    {
        return \PHPCore\Request::format();
    }
}

/**
 * Get data from request header
 *
 * Will return data from the HTTP request headers for a given **$key**. The
 * option **$filter** and **$options** parameters may be given to invoke
 * ``filter_var()`` before the value is returned.
 *
 * The key will be searched for both without then with the prefix "x-" to be
 * compatiable with older conventions. Therfore there is no need include the
 * prefix "x-" in your code moving forward.
 *
 * Supported Filters & Options:
 * https://www.php.net/manual/en/filter.filters.php
 *
 * @param string $key The key of the header's data to retrieve
 * @param integer $filter The ID of the filter to apply
 * @param array|int $options Associative array of options or bitwise disjunction
 *                           of flags
 * @return mixed The requested header item
 */
if ( ! in_array('request_header', $disabled_functions) ) {
    function request_header(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        return \PHPCore\Request::header($key, $filter, $options);
    }
}

/**
 * Get requester host name
 *
 * This method will return the requester's host name using the requester's ip
 * address, see ``Request::ip()`` for more information.
 *
 * Returns false if requester ip address is unknown.
 *
 * @return string|false Host name
 */
if ( ! in_array('request_host', $disabled_functions) ) {
    function request_host(): string|false
    {
        return \PHPCore\Request::host();
    }
}

/**
 * Get request ID
 *
 * Gets the unique identifier based on the **REQUEST_TIME_FLOAT**,
 * ``Request::ip()`` and the **REQUEST_URI**.
 *
 * @return string Request ID
 */
if ( ! in_array('request_id', $disabled_functions) ) {
    function request_id(): string
    {
        return \PHPCore\Request::id();
    }
}

/**
 * Get requester ip address
 *
 * This method will return the requester's ip address via the designated
 * ``$_SERVER`` param that contains the requester's IP Address. This is normally
 * REMOTE_ADDR or HTTP_X_FORWARDED_FOR and can be configured in the phpcore.ini
 * file.
 *
 * Returns false if ``$_SERVER`` param is not set.
 *
 * @return string|false IP Address of requester
 */
if ( ! in_array('request_ip', $disabled_functions) ) {
    function request_ip(): string|false
    {
        return \PHPCore\Request::ip();
    }
}

/**
 * Get parameter from requested URI
 *
 * This method will return the variable passed to the current script via the URL
 * parameters (aka. query string) by a given **$key** using ``$_GET``
 * superglobal varable. If the key is not passed then an array of all the
 * variables will be returned.
 *
 * If **$key** is not passed the entire query be returned and the **$filter**
 * and **$options** will be ignored.
 *
 * Supported Filters & Options:
 * https://www.php.net/manual/en/filter.filters.php
 *
 * @param string $key The key of the query to retrieve
 * @param integer $filter The ID of the filter to apply
 * @param array|int $options Associative array of options or bitwise
 *                           disjunction of flags
 * @return mixed The requested query item
 */
if ( ! in_array('request_param', $disabled_functions) ) {
    function request_param(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
    {
        return \PHPCore\Request::param($key, $filter, $options);
    }
}

/**
 * Get segment from requested URI
 *
 * This method will return a segment of the requested URI with a given **$pos**
 * using the **REQUEST_URI**.
 *
 * If **$pos** is not passed the entire segment array will be returned and the
 * **$filter** and **$options** will be ignored.
 *
 * Supported Filters & Options:
 * https://www.php.net/manual/en/filter.filters.php
 *
 * @param integer $pos The pos index of the path to retrieve
 * @param integer $filter The ID of the filter to apply
 * @param array|int $options Associative array of options or bitwise disjunction
 *                           of flags
 * @return mixed The requested segment item
 */
if ( ! in_array('request_segment', $disabled_functions) ) {
    function request_segment(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
    {
        return \PHPCore\Request::segment($pos, $filter, $options);
    }
}

// TODO: document
if ( ! in_array('response_add', $disabled_functions) ) {
    function response_add(string|array $key, mixed $data = null): void
    {
        \PHPCore\Response::add($key, $data);
    }
}

// TODO: document
if ( ! in_array('response_error', $disabled_functions) ) {
    function response_error(float $code, array $params = [], int $flags = 0): void
    {
        \PHPCore\Response::error($code, $params, $flags);
    }
}

// TODO: document
if ( ! in_array('response_send', $disabled_functions) ) {
    function response_send(mixed $data = null, ?int $statusCode = null): void
    {
        \PHPCore\Response::send($data, $statusCode);
    }
}

/**
 * Get session class instance
 *
 * Returns the current session instance. If the session has not been started yet it will be started
 * before the instance is returned.
 *
 * @return object Session
 */
if ( ! in_array('session', $disabled_functions) ) {
    function &session(): object
    {
        return \PHPCore\Session::getInstance();
    }
}

/**
 * Destroy all sessions
 *
 * Destroys **ALL** sessions if the save handlers supports this method.
 *
 * @return boolean Returns true on success or false on failure.
 * @throws Exception If save handler does not support this method.
 */
if ( ! in_array('session_destroy_all', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_destroy_all(): bool
    {
        return \PHPCore\Session::getInstance()->destroyAll();
    }
}

/**
 * Get session flash data item
 *
 * This method will return the flash data item that matches the provided key. If a key is not
 * provided the entire flash data array will be returned.
 *
 * @param string $key The key of the flash data item to retrieve
 * @return mixed Returns the flash data item
 */
if ( ! in_array('session_flash_get', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_get(?string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->flashGet($key);
    }
}

/**
 * Keep session flash data item
 *
 * This method will keep a session flash data item for the next session.
 *
 * @param string $key The key of the flash data item to keep
 * @return boolean Return true on success and false if not found
 */
if ( ! in_array('session_flash_keep', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_keep(string $key): bool
    {
        return \PHPCore\Session::getInstance()->flashKeep($key);
    }
}

/**
 * Set session flash data item
 *
 * This method will set a session flash data item to be used for the next session.
 *
 * @param string $key The key of the flash data item
 * @param mixed $value The value of the flash data item
 * @return void
 */
if ( ! in_array('session_flash_set', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_set(string $key, mixed $value): void
    {
        \PHPCore\Session::getInstance()->flashSet($key, $value);
    }
}

/**
 * Get session data item
 *
 * This method is used to retrieve a session data item.
 *
 * @param string $key Key of session data item to retrieve
 * @return mixed Data item from session data
 */
if ( ! in_array('session_get', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_get(string $key): mixed
    {
        return \PHPCore\Session::getInstance()->get($key);
    }
}

/**
 * Returns all the session metadata
 *
 * This method will get metadata with a provided key. If no key is passed the
 * entire metadata array will be returned.
 *
 * @param string $key Metadata Key
 * @return mixed Session Metadata
 */
if ( ! in_array('session_get_metadata', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_get_metadata(?string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->getMetadata($key);
    }
}

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
 */
if ( ! in_array('session_grant', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_grant(string|array $groups): void
    {
        \PHPCore\Session::getInstance()->grant($groups);
    }
}

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
 */
if ( ! in_array('session_revoke', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_revoke(string|array $groups): void
    {
        \PHPCore\Session::getInstance()->revoke($groups);
    }
}

/**
 * Set session data item
 *
 * This method is used to store a session data item. If the optional ``$ttl``
 * is passed the data item will also be given an expiration.
 *
 * @param string $key Key of session data item to set.
 * @param mixed $value Value of session data item to set.
 * @param integer $ttl Time To Live for this data item.
 */
if ( ! in_array('session_set', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_set(string $key, mixed $value, ?int $ttl = null): void
    {
        \PHPCore\Session::getInstance()->set($key, $value, $ttl);
    }
}

/**
 * Set the session metadata
 *
 * This method is used to store a session metadata data item.
 *
 * @param string $key Key of session data item to set.
 * @param mixed $value Value of session data item to set.
 */
if ( ! in_array('session_set_metadata', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_set_metadata(string $key, mixed $value): void
    {
        \PHPCore\Session::getInstance()->setMetadata($key, $value);
    }
}

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
if ( ! in_array('str_color', $disabled_functions) ) {
    function str_color(string $string, string $str_color_name, string $bkg_color_name = 'black'): string
    {
        switch ($str_color_name) {
            case 'black':         $text_color = '0;30'; break;
            case 'dark_grey':     $text_color = '1;30'; break;
            case 'red':           $text_color = '0;31'; break;
            case 'light_red':     $text_color = '1;31'; break;
            case 'green':         $text_color = '0;32'; break;
            case 'light_green':   $text_color = '1;32'; break;
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
if ( ! in_array('str_style', $disabled_functions) ) {
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
 * Time to array
 *
 * Takes a provide time and returns an array of time units.
 *
 * @param integer $time Time
 * @return array
 */
if ( ! in_array('timetoarray', $disabled_functions) ) {
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

/**
 * Get user class instance
 *
 * Returns the current user instance.
 *
 * @return object Session
 */
if ( ! in_array('user', $disabled_functions) and ! in_array('User', $disable_classes)) {
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
 */
if ( ! in_array('user_has_role', $disabled_functions) and ! in_array('User', $disable_classes)) {
    function user_has_role(string|array $groups, int $flags = 0): bool
    {
        return user()->hasAccess($groups, $flags);
    }
}

// TODO: document
if ( ! in_array('user_add_role', $disabled_functions) and ! in_array('User', $disable_classes)) {
    function user_add_role(string|array $roles, int $flags = 0): void
    {
        user()->addRole($roles, $flags);
    }
}

// TODO: document
if ( ! in_array('user_remove_role', $disabled_functions) and ! in_array('User', $disable_classes)) {
    function user_remove_role(string|array $roles): void
    {
        user()->removeRole($roles);
    }
}

/**
 * Returns the XML representation of a array
 *
 * @param array $array Array to be encoded as XML
 * @return string Returns a string containing the XML representation of the supplied array.
 */
if ( ! in_array('xml_encode', $disabled_functions) ) {
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

unset($disabled_functions);
unset($disable_classes);

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
