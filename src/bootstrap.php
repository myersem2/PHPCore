<?php
/**
 * PHPCore - Bootstrap
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 * @link https://manual.phpcore.org/bootstrap
 */

// -----------------------------------------------------------------------------

$config_path = $base_config = '/etc/phpcore/phpcore.ini';

/**
 * Load PHPCore configuration
 */

// base configuration
$ini_config = null;
if (file_exists($base_config) === true) {
    $ini_config = parse_ini_file('/etc/phpcore/phpcore.ini', true);
} else {
    trigger_error("Missing base PHPCore configuration file. ($base_config)", E_USER_WARNING);
}

// merge ini helper function
$merge_ini = function($alt_ini_config) use(&$ini_config) {
    foreach ($alt_ini_config as $section=>$directives) {
        foreach ($directives as $directive=>$value) {
            if (isset($ini_config[$section]) === false) {
                $ini_config[$section] = [];
            }
            $ini_config[$section][$directive] = $value;
        }
    }
};

// env configuration
$phpcorerc = getenv('PHPCORERC');
if (isset($phpcorerc) === true) {
    if (file_exists($phpcorerc) === true) {
        $config_path = $phpcorerc;
        $merge_ini(parse_ini_file($phpcorerc, true));
    }
}

// working path configuration
$wd = getcwd();
if (file_exists("$wd/phpcore.ini") === true) {
    $config_path = "$wd/phpcore.ini";
    $merge_ini(parse_ini_file("$wd/phpcore.ini", true));
}

// GLOBAL CORE
$GLOBALS['_CORE_INI'] = $ini_config;
$GLOBALS['_CORE'] = [
    'PATH'                => __DIR__,
    'CONFIG_FILE'         => $config_path,
    'DISABLE_FUNCTIONS'   => $ini_config['PHPCore']['disable_functions'] ?? '',
    'DISABLE_CLASSES'     => $ini_config['PHPCore']['disable_classes'] ?? '',
];

// TODO: load autoloader

// EOF /////////////////////////////////////////////////////////////////////////
