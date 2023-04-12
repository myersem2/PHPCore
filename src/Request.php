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
 * The Request class is used to simplify working with data send via the http
 * protocal.
 *
 * @see https://manual.phpcore.org/class/request
 */
final class Request
{

    /**
     * Get request agent capabilities
     *
     * Attempts to determine the capabilities of the user's browser, by looking
     * up the browser's information in the browscap.ini file. Then returns the
     * capability by the given ``$key``.
     *
     * If $key is not passed the entire capabilities object will be returned.
     *
     * Returns **NULL** if get_browser() fails or requested capability is
     * unknown.
     *
     * @param string $key The key of the capability data item to retrieve
     * @return mixed The request capability or the entire capability object
     */
    public static function agent(?string $key = null): mixed
    {
        static $agent;
        static $booleans = [
            'activexcontrols','alpha','backgroundsounds','beta','cookies',
            'crawler','frames','javascript','iframes','isanonymized','isfake',
            'ismobiledevice','ismodified','issyndicationreader','istablet',
            'javaapplets','tables','vbscript','win16','win32','win64',
        ];
        static $integers = [
            'aolversion','browser_bits','cssversion','majorver','minorver',
            'platform_bits',
        ];

        if ( ! isset($agent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = get_browser() ?? null;
            if (isset($agent)) {
                foreach ($booleans as $prop) {
                    $agent->$prop = boolval($agent->$prop);
                }
                foreach ($integers as $prop) {
                    $agent->$prop = intval($agent->$prop);
                }
            }
        }

        if (isset($key)) {
            return $agent->$key ?? null;
        }

        return $agent;
    }

    /**
     * Get data from request body
     *
     * Will parsed the request body based on the format, then return data from
     * the parsed body by a given $key for data passed via the HTTP POST method.
     * The option ``$filter`` and ``$options`` parameters may be given to invoke
     * filter_var() before the value is returned.
     *
     * If ``$key`` is not passed the request body be returned and the
     * ``$filter`` and ``$options`` will be ignored.
     *
     * Supported Filters & Options:
     * https://www.php.net/manual/en/filter.filters.php
     *
     * @param string $key The key of the body's data to retrieve
     * @param integer $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     * @return mixed The requested data item
     */
    public static function body(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
    {
        static $body;

        if ( ! isset($body)) {
            if ($rawBody = @file_get_contents('php:/'.'/input')) {
                $body = match (self::format()) {
                    'xml'   => @simplexml_load_string($rawBody),
                    'json'  => @json_decode($rawBody),
                    'yaml'  => @yaml_parse($rawBody),
                    default => null,
                } ?? $_POST;
            }
        }

        if (isset($key)) {
            $value = match (true) {
                is_array($body)  => $body[$key] ?? null,
                is_object($body) => $body->$key ?? null,
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
     * Will return data from cookie by a given $key for data passed via HTTP
     * Cookies. The option ``$filter`` and ``$options`` parameters may be given
     * to invoke filter_var() before the value is returned.
     *
     * Supported Filters & Options:
     * https://www.php.net/manual/en/filter.filters.php
     *
     * @param string $key The key of the body's data to retrieve
     * @param integer $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     * @return mixed The requested data item
     */
    public static function cookie(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        $value = $_COOKIE[$key] ?? null;

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get file from request
     *
     * Will return the file by a given $key for the files that was uploaded via
     * the HTTP POST method using the $_FILES superglobal variable.
     *
     * @param string $key The key of the file to retrieve
     * @return object|null RequestFile object
     */
    public static function file(string $key): object|null
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
     * Will return an array of files for a given $key that were uploaded via the
     * HTTP POST method using the $_FILES superglobal variable.
     *
     * @param string $key The key of the array of files to retrieve
     * @return array Array of RequestFile objects
     */
    public static function files(string $key): array
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
     * This method will return the request format by first looking at the
     * requested CONTENT_TYPE, if unknown then it will attempt to decipher using
     * the REQUEST_URI extention. If format cannot be determine then the
     * default_format set in the INI will be used.     
     *
     * @return string Format extention
     */
    public static function format(): string
    {
        static $extension;
        static $format;
        static $contentType;

        if ( ! isset($extension)) {
            list($path) = explode('?', $_SERVER['REQUEST_URI'] ?? '');
            $dotLocation = strripos($path, '.');
            if ($dotLocation !== false) {
                $extension = strtolower(substr($path, $dotLocation + 1));
            }
        }

        if ( ! isset($contentType)) {
            $contentType = match ($_SERVER['CONTENT_TYPE'] ?? null) {
                'text/json', 'application/json'     => 'json',
                'application/x-www-form-urlencoded' => 'xml',
                'text/yaml', 'application/yaml'     => 'yaml',
                'text/csv'                          => 'csv',
                default => null
            };
        }

        $format = match(true) {
            isset($contentType) => $contentType,
            isset($extension)   => $extension,
            default             => null,
        };

        if (empty($format)) {
            $format = $GLOBALS['_CORE']['FORMAT'] ?? 'json';
        }

        return $format;
    }

    /**
     * Get data from request header
     *
     * Will return data from the HTTP request headers for a given $key. The 
     * option ``$filter`` and ``$options`` parameters may be given to invoke
     * filter_var() before the value is returned.
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
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     * @return mixed The requested header item
     */
    public static function header(string $key, ?int $filter = null, array|int $options = 0): mixed
    {
        static $headers;

        if (empty($headers)) {
            foreach (getallheaders() as $index=>$value) {
                $headers[strtoupper($index)] = $value;
            }
        }

        $value = match (true) {
            isset($headers[strtoupper($key)])      => $headers[strtoupper($key)]      ?? null,
            isset($headers['X-'.strtoupper($key)]) => $headers['X-'.strtoupper($key)] ?? null,
            default => null,
        };

        if (isset($filter)) {
            $value = filter_var($value, $filter, $options);
        }

        return $value;
    }

    /**
     * Get requester host name
     *
     * This method will return the requester's host name using the requester's
     * ip address, see Request::ipAddress() for more information.
     *
     * Returns false if requester ip address is unknown.
     *
     * @return string|false Host name
     */
    public static function host(): string|false
    {
        $ip = self::ip();
        if ($ip !== false) {
          return @gethostbyaddr($ip);
        } else {
          return false;
        }
    }

    // TODO: Document
    public static function id(): string
    {
        static $id;

        if ( ! isset($id)) {
            $id = md5($_SERVER['REQUEST_TIME_FLOAT'].'['.self::ip().']'.$_SERVER['REQUEST_URI']);
        }

        return $id;
    }

    /**
     * Get requester ip address
     *
     * This method will return the requester's ip address via the designated
     * $_SERVER param that contains the requester's IP Address. This is normally
     * REMOTE_ADDR or HTTP_X_FORWARDED_FOR and can be configured in the 
     * phpcore.ini file.
     *
     * Returns false if $_SERVER param is not set.
     *
     * @return string|false IP Address of requester
     */
    public static function ip(): string|false
    {
        static $ip;

        if ( ! isset($ipAaddress)) {
            $svr_var = core_ini_get('request.ip_var');
            $ip = match (true) {
                isset($_SERVER[$svr_var]) => $_SERVER[$svr_var],
                default                   => false,
            };
        }

        return $ip;
    }

    /**
     * Get parameter from requested URI
     *
     * This method will return the variable passed to the current script via the
     * URL parameters (aka. query string) by a given $key using $_GET
     * superglobal varable. If the key is not passed then an array of all the
     * variables will be returned.
     *
     * If ``$key`` is not passed the entire query be returned and the
     * ``$filter`` and ``$options`` will be ignored.
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
    public static function param(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
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
     * Get segment from requested URI
     *
     * This method will return a segment of the requested URI with a given $pos
     * using the REQUEST_URI.
     *
     * If ``$pos`` is not passed the entire segment array will be returned and
     * the ``$filter`` and ``$options`` will be ignored.
     *
     * Supported Filters & Options:
     * https://www.php.net/manual/en/filter.filters.php
     *
     * @param integer $pos The pos index of the path to retrieve
     * @param integer $filter The ID of the filter to apply
     * @param array|int $options Associative array of options or bitwise
     *                           disjunction of flags
     * @return mixed The requested segment item
     */
    public static function segment(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
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

// -----------------------------------------------------------------------------

/**
 * Request File Class
 *
 * The RequestFile class is used internally for the Request class for the file
 * and files method.
 */
final class RequestFile
{
    /**
     * True Type
     * @var string
     */
    private $true_type = 'UNKNOWN';

    // ---------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct($file)
    {
        foreach (array_keys($file) as $key) {
            $this->$key = $file[$key];
        }

        if ( ! is_uploaded_file($this->tmp_name) && empty($this->error)) {
            $this->error = 5;
        }
    }

    /**
     * Get file contents
     *
     * This method will invoke file_get_contents() on the file using tmp_name
     * to return the file contents as a string.
     *
     * If there is no file or if there was an error uploading NULL will be
     * returned.
     *
     * @return string File contents as a string
     */
    public function getContents(): string|null
    {
        if (empty($this->tmp_name) || ! empty($this->error)) {
            return null;
        }
        return file_get_contents($this->tmp_name);
    }

    /**
     * Get upload error
     *
     * This method will invoke file_get_contents() on the file using tmp_name
     * to return the file contents as a string.
     *
     * If there is no error NULL will be returned.
     *
     * @return string Error message
     */
    public function getError(): string|null
    {
        switch($this->error) {
          case UPLOAD_ERR_OK:
                return null;
          break;
          case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
          break;
          case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
          break;
          case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
          break;
          case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
          break;
          case 5:
                return 'File was not uploaded via HTTP POST';
          break;
          case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
          break;
          case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
          break;
          case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
          break;
          default:
                return 'There was a problem with your upload.';
          break;
        }
    }

    /**
     * File true type
     *
     * This method uses PHP finfo class to determine the uploaded file's true
     * type.
     *
     * @see https://www.php.net/manual/en/class.finfo.php
     * 
     * @return string Error message
     */
    public function trueType(): string
    {
        static $finfo;

        if ( ! isset($finfo)) {
            $finfo = new \finfo(FILEINFO_MIME);
        }

        if (empty($this->true_type) && ! empty($this->tmp_name)) {
            list($this->true_type) = explode(';', $finfo->buffer(file_get_contents($this->tmp_name)));
        }

        return $this->true_type;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
