<?php
/**
 * PHPCore - Bootstrap
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 * @link https://manual.phpcore.org/bootstrap
 */

// -------------------------------------------------------------------------------------------------

// =============================================================================
// PHPCore defines
// =============================================================================
define('CORE_MAJOR_VERSION', 1);
define('CORE_MINOR_VERSION', 0);
define('CORE_RELEASE_VERSION', 0);
define('CORE_VERSION_ID', 10000);
define('CORE_EXTRA_VERSION', '-beta');
define(
    'CORE_VERSION',
    CORE_MAJOR_VERSION . '.' . CORE_MINOR_VERSION . '.' . CORE_RELEASE_VERSION . CORE_EXTRA_VERSION
);

// =============================================================================
// Base variables
// =============================================================================
$config_paths[] = $config_path = $base_config = '/etc/phpcore/phpcore.ini';
$ini_config = null;

// =============================================================================
// Helper functions
// =============================================================================

/**
 * Merge INI helper function
 *
 * This helper function is used to add/override directives from alternate
 * PHPCore configuration sources.
 *
 * @param array $alt_ini_config Alternate PHPCore INI configuration
 * @return void
 */
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

// =============================================================================
// PHPCore configurations
// =============================================================================

// Load Base PHPCore configuration
if (is_readable($base_config) === true) {
    $ini_config = parse_ini_file('/etc/phpcore/phpcore.ini', true);
} else {
    trigger_error(
        "Missing base PHPCore configuration file. ($base_config)",
        E_USER_WARNING
    );
}

// Look for and load environment PHPCore configuration
$phpcorerc = getenv('PHPCORERC');
if (isset($phpcorerc) === true) {
    if (is_readable($phpcorerc) === true) {
        $config_path = $phpcorerc;
        $config_paths[] = $config_path;
        $merge_ini(parse_ini_file($phpcorerc, true));
    }
}

// Look for and load working path PHPCore configuration
$wd = getcwd();
if (is_readable("$wd/phpcore.ini") === true) {
    $config_path = "$wd/phpcore.ini";
    $config_paths[] = $config_path;
    $merge_ini(parse_ini_file("$wd/phpcore.ini", true));
}

// version lock check
if (empty($ini_config['PHPCore']['version_lock']) === false) {
    if (str_starts_with(CORE_VERSION, $ini_config['PHPCore']['version_lock']) === false) {
        trigger_error(
            "PHPCore configuration version lock mismatch with this version.",
            E_USER_WARNING
        );
    }
}

// =============================================================================
// PHPCore globals $_CORE / $_CORE_INI
// =============================================================================
$set_globals = function(array $ini_config, string $config_path = null, array $config_paths = null) {
    $GLOBALS['_CORE_INI'] = $ini_config;
    $config_path = $config_path ?? $GLOBALS['_CORE']['CONFIG_FILE'];
    $config_paths = $config_paths ?? explode(',', $GLOBALS['_CORE']['CONFIG_FILES']);
    $GLOBALS['_CORE'] = [
        'PATH'                => __DIR__,
        'CONFIG_FILE'         => $config_path,
        'CONFIG_FILES'        => implode(',', $config_paths),
        'DISABLE_FUNCTIONS'   => $ini_config['PHPCore']['disable_functions'] ?? '',
        'DISABLE_CLASSES'     => $ini_config['PHPCore']['disable_classes'] ?? '',
    ];
};
$set_globals($ini_config, $config_path, $config_paths);

// =============================================================================
// Autoloader
// =============================================================================
include $GLOBALS['_CORE']['PATH'] . DIRECTORY_SEPARATOR . 'functions.php';
spl_autoload_register(function(string $class_name) {
    if ($class_name[0] === '\\') {
        $class_name = substr($class_name, 1);
    }
    if (str_starts_with($class_name, 'PHPCore\\')) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class_name, 7));
        if (empty($GLOBALS['_CORE']['DISABLE_CLASSES']) === false) {
            if (in_array($file, explode(',', $GLOBALS['_CORE']['DISABLE_CLASSES'])) === true) {
                return;
            }
        }
        include $GLOBALS['_CORE']['PATH'] . "$file.php";
    }
});

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
