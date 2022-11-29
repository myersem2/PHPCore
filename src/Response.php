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
     * Header Queue
     *
     * @var array
     */
    private static $header_queue = [];

    /**
     * Prevent page caching
     *
     * @var boolean
     */
    private static $prevent_caching = true;

    // -----------------------------------------------------------------------------------------

    /**
     * Add Header
     *
     * Add a header to the queue that will be sent when the response sent.
     *
     * @param string $header Header
     * @param boolean $replace Replace
     * @return void
     */
    public static function addHeader(string $header, ?bool $replace = true): void
    {
        list($type) = explode(':', $header);
        self::$header_queue[md5($header)] = [
            'type'    => $type,
            'string'  => $header,
            'replace' => $replace,
        ];
    }

    /**
     * Remove Header
     *
     * Remove a header from the queue that will be sent when the response sent.
     * Note the header string passed MUST EXACTLY match the string that was used
     * to add the header unless the exact_match param is set to false.
     *
     * @param string $header Header
     * @param boolean $exact_match Header
     * @return void
     */
    public static function removeHeader(string $header, ?bool $exact_match = true): void
    {
        if ($exact_match) {
            unset(self::$header_queue[md5($header)]);
        } else {
            list($type) = explode(':', $header);
            foreach (self::$header_queue as $index=>$header) {
                if ($header['type'] === $type) {
                    unset(self::$header_queue[$index]);
                }
            }
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
    public static function send(?string $body = null): void
    {
        self::sendHeaders();
        echo $body;
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

      header('X-Powered-By: ACore');

      if (self::$prevent_caching) {
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

      foreach (self::$header_queue as $header) {
          header($header['string'], $header['replace']);
      }

    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
