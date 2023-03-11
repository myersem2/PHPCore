<?php declare(strict_types=1);
/**
 * PHPCore - Controller
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Controller Class
 */
final class Controller
{
    use Core;

    /**
     * Controller Error
     *
     * @param integer $code HTTP response status code
     * @param array $errorData Error Data (i.e. {"field":"username","message":"Username in use."})
     * @param string $category HTTP response status category
     * @return void
     */
    public static function error(int $code, ?array $errorData = null, ?string $category = null): void
    {
        http_response_code($code);
        switch($code) {
  
          // Information responses
          case 100: $category = $category ?? "Continue"; break;
          case 101: $category = $category ?? "Switching Protocols"; break;
          case 102: $category = $category ?? "Processing"; break;
          case 103: $category = $category ?? "Early Hints"; break;

          // Successful responses
          case 200: $category = $category ?? "OK"; break;
          case 201: $category = $category ?? "Created"; break;
          case 202: $category = $category ?? "Accepted"; break;
          case 203: $category = $category ?? "Non-Authoritative Information"; break;
          case 204: $category = $category ?? "No Content"; break;
          case 205: $category = $category ?? "Reset Content"; break;
          case 206: $category = $category ?? "Partial Content"; break;
          case 207: $category = $category ?? "Multi-Status"; break;
          case 208: $category = $category ?? "Already Reported"; break;
          case 226: $category = $category ?? "IM Used"; break;

          // Redirection messages
          case 300: $category = $category ?? "Multiple Choices"; break;
          case 301: $category = $category ?? "Moved Permanently"; break;
          case 302: $category = $category ?? "Found"; break;
          case 303: $category = $category ?? "See Other"; break;
          case 304: $category = $category ?? "Not Modified"; break;
          case 305: $category = $category ?? "Use Proxy"; break;
          case 306: $category = $category ?? "Unused"; break;
          case 307: $category = $category ?? "Temporary Redirect"; break;
          case 308: $category = $category ?? "Permanent Redirect"; break;

          // Client error responses
          case 400: $category = $category ?? "Bad Request"; break;
          case 401: $category = $category ?? "Unauthorized"; break;
          case 402: $category = $category ?? "Payment Required"; break;
          case 403: $category = $category ?? "Forbidden"; break;
          case 404: $category = $category ?? "Not Found"; break;
          case 405: $category = $category ?? "Method Not Allowed"; break;
          case 406: $category = $category ?? "Not Acceptable"; break;
          case 407: $category = $category ?? "Proxy Authentication Required"; break;
          case 408: $category = $category ?? "Request Timeout"; break;
          case 409: $category = $category ?? "Conflict"; break;
          case 410: $category = $category ?? "Gone"; break;
          case 411: $category = $category ?? "Length Required"; break;
          case 412: $category = $category ?? "Precondition Failed"; break;
          case 413: $category = $category ?? "Payload Too Large"; break;
          case 414: $category = $category ?? "URL Too Long"; break;
          case 415: $category = $category ?? "Unsupported Media Type"; break;
          case 416: $category = $category ?? "Range Not Satisfiable"; break;
          case 417: $category = $category ?? "Expectation Failed"; break;
          case 418: $category = $category ?? "I'm a teapot"; break;
          case 421: $category = $category ?? "Misdirected Request"; break;
          case 422: $category = $category ?? "Unprocessable Entity"; break;
          case 423: $category = $category ?? "Locked"; break;
          case 424: $category = $category ?? "Failed Dependency"; break;
          case 425: $category = $category ?? "Too Early"; break;
          case 426: $category = $category ?? "Upgrade Required"; break;
          case 428: $category = $category ?? "Precondition Required"; break;
          case 429: $category = $category ?? "Too Many Requests"; break;
          case 431: $category = $category ?? "Request Header Fields Too Large"; break;
          case 451: $category = $category ?? "Unavailable For Legal Reasons"; break;

          // Server error responses [ DEFAULT ]
          default:
          case 500: $category = $category ?? "Internal Server Error";  break;
          case 501: $category = $category ?? "Not Implemented"; break;
          case 502: $category = $category ?? "Bad Gateway"; break;
          case 503: $category = $category ?? "Service Unavailable"; break;
          case 504: $category = $category ?? "Gateway Timeout"; break;
          case 505: $category = $category ?? "HTTP Version Not Support"; break;
          case 506: $category = $category ?? "Variant Also Negotiates"; break;
          case 507: $category = $category ?? "Insufficient Storage"; break;
          case 508: $category = $category ?? "Loop Detected"; break;
          case 510: $category = $category ?? "Not Extended"; break;
          case 511: $category = $category ?? "Network Authentication Required"; break;
        }
        Response::send(array_merge([
          'errorCode' => $code,
          'errorCategory' => $category,
        ], $errorData));
        exit;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
