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
     * Headers that are queued to be sent
     *
     * @var array
     */
    private static $headerQueue = [];

    /**
     * Prevent client from page caching
     *
     * @var boolean
     */
    private static $preventCaching = true;

    // -----------------------------------------------------------------------------------------

    /**
     * Add Header
     *
     * Add a header to the header queue that will be sent right before the
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
        self::$headerQueue[$key] = [
            'name'    => $name,
            'string'  => $header,
            'replace' => $replace,
        ];
    }

    /**
     * Remove Header
     *
     * Remove a header from the header queue that will be sent when the response
     * sent.
     *
     * @param string $header Header
     * @param boolean $byName By Name
     * @return void
     */
    public static function removeHeader(string $header, bool $byName = false): void
    {
        if ($byName) {
            list($name) = explode(':', $header);
            foreach (self::$headerQueue as $key => $header) {
                if ($header['name'] === $name) {
                    unset(self::$headerQueue[$key]);
                }
            }
        } else {
            $key = md5(strtolower(trim($header)));
            unset(self::$headerQueue[$key]);
        }
    }

    /**
     * Send Response
     *
     * Send the headers and response body to the browser.
     *
     * @param string $body Response body
     * @return void
     */
    public static function send(?mixed $data = null): void
    {
        self::sendHeaders();
        if (is_string($data)) {
            echo $data;
        } elseif (is_array($data)) {
            foreach ($data as $item) {
                echo $item->export();
            }
        } elseif (is_sset($data)) {
            echo $data->export();
        }
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

      header('X-Powered-By: PHPCore');

      if (self::$preventCaching) {
          header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
          header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
          header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
          header('Cache-Control: post-check=0, pre-check=0', false);
          header('Pragma: no-cache');
      }
      
      switch ($GLOBALS['_CORE']['FORMAT']) {
          case 'json':
              header('Content-type: application/json');
          break;
          case 'xml':
              header('Content-type: text/xml');
          break;
      }

      foreach (self::$headerQueue as $header) {
          header($header['string'], $header['replace']);
      }

    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
