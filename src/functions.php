<?php
/**
 * PHPCore - Functions
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -----------------------------------------------------------------------------

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
function core_ini_get(string $directive, string $section = 'PHPCore'): string|false
{
    return $GLOBALS['_CORE_INI'][$section][$directive] ?? false;
}

/**
 * Gets all configuration options
 *
 * Returns all the registered configuration options.
 *
 * @param string $section An optional section name. If not null, the function
 *                        returns only options specific for that section.
 * @return array Returns an associative array with directive name as the array
 *               key. Returns false and raises an E_WARNING level error if the
 *               section doesn't exist.
 */
function core_ini_get_all(string $section = null): array|false
{
    return $GLOBALS['_CORE_INI'][$section] ?? false;
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
function core_ini_set(string $directive, string|int|float|bool|null $value, string $section = 'PHPCore'): string|false
{
    $oldValue =  $GLOBALS['_CORE_INI'][$section][$directive] ?? '';
    if (isset($GLOBALS['_CORE_INI'][$section])) {
      $GLOBALS['_CORE_INI'][$section] = [];
    }  
    $GLOBALS['_CORE_INI'][$section][$directive] = $value;
    return $oldValue;
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
 * @param string $dsn Data Source Name (DSN) string to parse .
 * @return array Returns DSN elements as associated array.
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
