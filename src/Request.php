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
 */
final class Request
{
    use Core;

    public static function process()
    {
        // Set Format
        list($request_name) = explode('?', $_SERVER['REQUEST_URI']);
        $pos = strrpos($request_name, '.');
        if ($pos !== false) {
          $GLOBALS['_CORE']['FORMAT'] = strtolower(substr($request_name, $pos+1));
        }
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
