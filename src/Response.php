<?php declare(strict_types=1);
/**
 * PHPCore - Response
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Response Class
 */
final class Response
{
    use Core;

    /**
     * Buffer to be sent
     *
     * @var array
     */
    private static $buffer = [];

    /**
     * Error buffer to be sent
     *
     * @var array
     */
    private static $errorBuffer = null;

    /**
     * Header buffer to be sent
     *
     * @var array
     */
    private static $headerBuffer = [];

    /**
     * Prevent client from page caching
     *
     * @var boolean
     */
    private static $preventCaching = true;

    // ---------------------------------------------------------------------

    // TODO: Document
    public static function add(string|array $key, mixed $data = null): void
    {
        if (is_array($key)) {
            foreach ($key as $index => $value) {
                self::$buffer[$index] = $value;
            }
        } else {
            self::$buffer[$key] = $data;
        }
    }

    /**
     * Add Header
     *
     * Add a header to the header buffer that will be sent right before the
     * response is sent.
     *
     * @param string $header Header
     * @param boolean $replace Replace
     * @return void
     */
    public static function addHeader(string $header, bool $replace = true): void
    {
        list($name) = explode(':', $header);
        $key = md5(strtolower(trim($header)));
        self::$headerBuffer[$key] = [
            'name'    => $name,
            'string'  => $header,
            'replace' => $replace,
        ];
    }

    /**
     * TODO: Document
     */
    public static function error(float $code, array $params = []): void
    {
        $data = [ 'code' => $code ];

        $status_codes_file = core_ini_get('response.status_codes');
        if (empty($status_codes_file) || ! file_exists($status_codes_file)) {
            trigger_error('PHPCore configuration directive response.status_codes is not valid.', E_USER_ERROR);
        }
        $status_codes = json_decode(file_get_contents($status_codes_file), true);
        if (empty($status_codes)) {
            trigger_error('PHPCore configuration directive response.status_codes is not valid.', E_USER_ERROR);
        }

        if (isset($status_codes[strval($code)])) {
            $data = array_merge($data, $status_codes[strval($code)]);
        }

        foreach ($params as $key => $value) {
            foreach ($data as &$item) {
                if (is_string($item)) {
                    $item = str_replace('{'.strtoupper($key).'}', $value, $item);
                }
            }
        }

        self::$errorBuffer = $data;
        self::send();

    }

    /**
     * TODO: Document
     */
    public static function export(mixed $data): string
    {
      switch (Request::format()) {
          default:
              trigger_error('Unknown format '.Request::format(), E_USER_ERROR);
          break;
          case 'html':
              return var_export($data) ?? '';
          break;
          case 'json':
              return json_encode($data, JSON_UNESCAPED_UNICODE) ?? '';
          break;
          case 'xml':
              return xml_encode($data, XML_ENCODE_AS_XML_OBJ)->asXML() ?? '';
          break;
      }
    }

    /**
     * Remove Header
     *
     * Remove a header from the header buffer that will be sent when the
     * response sent.
     *
     * @param string $header Header
     * @param boolean $byName By Name
     * @return void
     */
    public static function removeHeader(string $header, bool $byName = false): void
    {
        if ($byName) {
            list($name) = explode(':', $header);
            foreach (self::$headerBuffer as $key => $header) {
                if ($header['name'] === $name) {
                    unset(self::$headerBuffer[$key]);
                }
            }
        } else {
            $key = md5(strtolower(trim($header)));
            unset(self::$headerBuffer[$key]);
        }
    }

    /**
     * Send Response
     *
     * Send the headers and buffer to the output buffer.
     *
     * @param mixed $data Data
     * @param integer $statusCode Status Code
     * @return void
     */
    public static function send(mixed $data = null, ?int $statusCode = null): void
    {
        if ( ! headers_sent()) {
            self::sendHeaders();
        }

        $baseResponse = [
            'apiVersion' => defined('API_VERSION') ? API_VERSION : '1.0',
            'id'         => Request::id(),
            'timestamp'  => date('c'),
            'method'     => implode('/', Request::segment()).'.'.strtolower($_SERVER['REQUEST_METHOD']),
        ];

        $context = Request::param('context');
        if (isset($context)) {
            $baseResponse['context'] = $context;
        }
        $params = Request::param();
        if ( ! empty($params)) {
          $baseResponse['params'] = $params;
        }

        if (isset(self::$errorBuffer)) {
            $statusCode = $statusCode ?? intval(floor(self::$errorBuffer['code']));
            self::$buffer['error'] = self::$errorBuffer;
        } else {
            $statusCode = $statusCode ?? 200;
            if (isset($data)) {
                self::$buffer['data'] = $data;
            }
        }

        http_response_code($statusCode);

        echo self::export(array_merge($baseResponse, self::$buffer));
        exit(intval(isset(self::$errorBuffer)));
    }

    /**
     * Send Response Headers
     *
     * Send the raw HTTP headers
     *
     * @return void
     */
    public static function sendHeaders(): void
    {

      header('Powered-By: PHPCore');

      if (self::$preventCaching) {
          header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
          header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
          header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
          header('Cache-Control: post-check=0, pre-check=0', false);
          header('Pragma: no-cache');
      }

      switch (Request::format()) {
          default:
              trigger_error('Unknown format '.Request::format(), E_USER_ERROR);
          break;
          case 'html': header('Content-Type: text/html');         break;
          case 'json': header('Content-type: application/json');  break;
          case 'xml':  header('Content-type: text/xml');          break;
      }

      foreach (self::$headerBuffer as $header) {
          header($header['string'], $header['replace']);
      }

    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
