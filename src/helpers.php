<?php
/**
 * PHPCore - Helpers
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -----------------------------------------------------------------------------

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
 * @param string $dsn DSN String
 * @return array
 */
function parse_dsn(string $dsn): array
{
    if (strpos($dsn, ':') === false) {
        throw new InvalidArgumentException(
            'parse_dsn function only accepts valid dsn strings'
        );
    }
    try {
        $dsn_parts = explode(':', $dsn);
        $driver = $dsn_parts[0];
        $params = $dsn_parts[1] ?? '';
        $output['driver'] = $driver;
        foreach(explode(';', $params) as $item) {
            if (empty($item) === true) {
                continue;
            }
            switch ($driver) {
                case 'sqlite':
                    $output['path'] = $item;
                    return $output;
                    break;
            }
            list($name, $value) = explode('=', $item);
            $output[$name] = $value;
        }
        return $output;
    } catch (Exception  $e) {
        throw new InvalidArgumentException(
            'parse_dsn function only accepts valid dsn strings'
        );
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
