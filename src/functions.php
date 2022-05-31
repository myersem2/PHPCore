<?php declare(strict_types=1);
/**
 * PHPCore - Functions
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

$disabled_functions = $disable_classes = [];
if (empty($GLOBALS['_CORE']['DISABLE_FUNCTIONS']) === false) {
    $disabled_functions = explode(',', $GLOBALS['_CORE']['DISABLE_FUNCTIONS']);
}
if (empty($GLOBALS['_CORE']['DISABLE_CLASSES']) === false) {
    $disable_classes = explode(',', $GLOBALS['_CORE']['DISABLE_CLASSES']);
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
    function core_ini_get_all(string|null $section = null, string|null $sub_section = null): array|false
    {
        if (empty($section)) {
            return $GLOBALS['_CORE_INI'] ?? false;
        } elseif(empty($sub_section)) {
            return $GLOBALS['_CORE_INI'][$section] ?? false;
        } else {
            $sub_directives = [];
            if (empty($GLOBALS['_CORE_INI'][$section]) === false) {
                foreach ($GLOBALS['_CORE_INI'][$section] as $directive=>$value) {
                    if (preg_match('/(\w*)\.(\w*)/', $directive, $matches)) {
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
    function core_ini_set(string $directive, string|int|float|bool|null $value, string $section = 'PHPCore'): string|false
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
 * Gets PHPCore Information
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
            break;
            /*
            // TODO: clean this up with a function or library to output HTML
            case 'html':
                $output .= "<h1>PHPCore $version</h1>$eol";
                $output .= "<h3>Core Configuration</h3>$eol";
                $output .= "<table>$eol";
                $output .= "    <thead>$eol";
                $output .= "        <tr>$eol";
                $output .= "            <td>Directive</td>$eol";
                $output .= "            <td>Value</td>$eol";
                $output .= "        </tr>$eol";
                $output .= "    </thead>$eol";
                $output .= "    <tbody>$eol";
                foreach ($GLOBALS['_CORE_INI'] as $section=>$directives) {
                    $output .= "        <tr><td colSPan=\"2\" style=\"font-weight:bold;\">$section</td></tr>$eol";
                    foreach ($directives as $directive=>$value) {
                        $output .= "        <tr><td>$directive</td><td>$value</td></tr>$eol";
                    }
                }
                $output .= "    </tbody>$eol";
                $output .= "</table>$eol";
                $output .= "<hr>$eol";
                $output .= "<h3>Core Variables</h3>$eol";
                $output .= "<table>$eol";
                $output .= "    <thead>$eol";
                $output .= "        <tr>$eol";
                $output .= "            <td>Item</td>$eol";
                $output .= "            <td>Value</td>$eol";
                $output .= "        </tr>$eol";
                $output .= "    </thead>$eol";
                $output .= "    <tbody>$eol";
                foreach ($GLOBALS['_CORE'] as $name=>$value) {
                    $output .= "        <tr><td>$name</td><td>$value</td></tr>$eol";
                }
                $output .= "    </tbody>$eol";
                $output .= "</table>$eol";
            break;
            */
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
            break;
        }
        echo $output;
    }
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
 * Get session flash data item
 *
 * @param string $key The key of the flash data item to retrieve
 * @return mixed
 */
if ( ! in_array('session_flash_get', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_get(string $key): mixed
    {
        return \PHPCore\Session::getInstance()->flashGet($key);
    }
}

/**
 * Keep session flash data item for the next session. Return true on success and
 * false if item was not found in flash data.
 *
 * @param string $key The key of the flash data item to retrieve
 * @return boolean
 */
if ( ! in_array('session_flash_keep', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_flash_keep(string $key): bool
    {
        return \PHPCore\Session::getInstance()->flashKeep($key);
    }
}

/**
 * Set session flash data item for use in the next session
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
 * Returns all the session metadata
 *
 * If no key is passed the entire metadata array will be returned.
 *
 * @param string $key Metadata Key
 * @return array Session Metadata
 */
if ( ! in_array('session_get_metadata', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_get_metadata(string $key = null): mixed
    {
        return \PHPCore\Session::getInstance()->getMetadata($key);
    }
}

/**
 * Grant session access to an ACL group
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
 * Check if session has ACL group
 *
 * @param string $group ACL group to check access for
 * @return boolean
 */
if ( ! in_array('session_has_access', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_has_access(string $group): bool
    {
        return \PHPCore\Session::getInstance()->hasAccess($group);
    }
}

/**
 * Revoke session access to an ACL group
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
 * Returns time left in session
 * 
 * If the gc_maxlength directive is set it will return the difference in time
 * since the session started. If directive is not used will return null.
 *
 * @return integer|null
 */
if ( ! in_array('session_time_left', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_time_left(): int|null
    {
        return \PHPCore\Session::getInstance()->timeLeft();
    }
}

/**
 * Get and/or set the current session user
 *
 * If the user parameter is used the acl_groups will be replaced with the ones
 * declared in the acl_group.default_user directive. The session start time will
 * also be reset.
 *
 * @param string|int $user The user to bind to the current session
 * @return string|int|null
 */
if ( ! in_array('session_user', $disabled_functions) and ! in_array('Session', $disable_classes)) {
    function session_user(string|int $user = null): string|int|null
    {
        if ($user !== null) {
            \PHPCore\Session::getInstance()->userBind($user);
        }
        $meta_data = \PHPCore\Session::getInstance()->getMetadata();
        return $meta_data['user'] ?? null;
    }
}

/**
 * String Color
 *
 * @param string $string         String to be colorized
 * @param string $str_color_name String color name
 * @param string $bkg_color_name Background color name
 * @return void
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
 * String Style
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
 * Returns the XML representation of a array 
 *
 * @param array $array Array to be encoded as XML
 * @return string Returns a string containing the XML representation of the supplied array.
 */
if ( ! in_array('xml_encode', $disabled_functions) ) {
    define('XML_ENCODE_AS_XML_OBJ', 1);
    define('XML_ENCODE_PRETTY_PRINT', 2);
    function xml_encode(array $array, int $flags = 0)
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
