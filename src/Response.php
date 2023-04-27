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
#[Documentation('../docs/classes/response.rst')]
final class Response
{
    use Core;

    /**
     * Buffer to be sent
     *
     * @var array
     */
    private static array $Buffer = [];

    /**
     * Error buffer to be sent
     *
     * @var array
     */
    private static ?array $ErrorBuffer = [];

    /**
     * Header buffer to be sent
     *
     * @var array
     */
    private static array $HeaderBuffer = [];

    /**
     * Prevent client from page caching
     *
     * @var boolean
     */
    private static bool $PreventCaching = true;

    // ---------------------------------------------------------------------

    /**
     * TODO: Document
     *
     * @param array|string $key Key
     * @param mixed $data Data to be added
     * @return void
     */
    public static function add(array|string $key, mixed $data = null): void
    {
        if (is_array($key)) {
            foreach ($key as $index => $value) {
                self::$Buffer[$index] = $value;
            }
        } else {
            self::$Buffer[$key] = $data;
        }
    }

    /**
     * Add Header
     *
     * Add a header to the header buffer that will be sent right before the
     * response is sent.
     *
     * @param string $header Header
     * @param bool $replace Replace
     * @return void
     */
    public static function addHeader(string $header, bool $replace = true): void
    {
        list($name) = explode(':', $header);
        $key = md5(strtolower(trim($header)));
        self::$HeaderBuffer[$key] = [
            'name'    => $name,
            'string'  => $header,
            'replace' => $replace,
        ];
    }

    /**
     * TODO: Document
     * @ignore
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

        self::$ErrorBuffer = $data;
        self::send();

    }

    /**
     * TODO: Document
     * @ignore
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
     * @ignore
     */
    public static function removeHeader(string $header, bool $byName = false): void
    {
        if ($byName) {
            list($name) = explode(':', $header);
            foreach (self::$HeaderBuffer as $key => $header) {
                if ($header['name'] === $name) {
                    unset(self::$HeaderBuffer[$key]);
                }
            }
        } else {
            $key = md5(strtolower(trim($header)));
            unset(self::$HeaderBuffer[$key]);
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
     * @ignore
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

        if (empty(self::$ErrorBuffer)) {
            $statusCode = $statusCode ?? 200;
            if (isset($data)) {
                self::$Buffer['data'] = $data;
            }
        } else {
            $statusCode = $statusCode ?? intval(floor(self::$ErrorBuffer['code']));
            self::$Buffer['error'] = self::$ErrorBuffer;
        }

        http_response_code($statusCode);

        echo self::export(array_merge($baseResponse, self::$Buffer));
        exit(intval( ! empty(self::$ErrorBuffer)));
    }

    /**
     * Send Response Headers
     *
     * Send the raw HTTP headers
     *
     * @return void
     * @ignore
     */
    public static function sendHeaders(): void
    {

      header('Powered-By: PHPCore');

      if (self::$PreventCaching) {
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

      foreach (self::$HeaderBuffer as $header) {
          header($header['string'], $header['replace']);
      }

    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
