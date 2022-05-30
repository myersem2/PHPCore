<?php declare(strict_types=1);
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
$ini_config = null;
$interface = str_starts_with(php_sapi_name(), 'cli') ? 'cli' : 'www';
$interface = str_starts_with(php_sapi_name(), 'apache2') ? 'apache2' : $interface;
$config_paths[] = $config_path = $base_config = "/etc/phpcore/$interface/phpcore.ini";

// =============================================================================
// PHPCoreAutoloader
// =============================================================================
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

// =============================================================================
// Load Base PHPCore configuration
// =============================================================================
if (is_readable($base_config) === true) {
    $ini_config = parse_ini_file($base_config, true);
} else {
    trigger_error(
        "Missing base PHPCore configuration file. ($base_config)",
        E_USER_WARNING
    );
}
unset($base_config);

// =============================================================================
// Look for and load environment PHPCore configuration
// =============================================================================
$phpcorerc = getenv('PHPCORERC');
if (empty($phpcorerc) === false) {
    if (is_readable($phpcorerc) === true) {
        $config_path = $phpcorerc;
        $config_paths[] = $config_path;
        $ini_config = array_merge_recursive($ini_config, parse_ini_file($phpcorerc, true), );
    }
}

// =============================================================================
// Look for and load working path PHPCore configuration
// =============================================================================
$wd = getcwd();
if (is_readable("$wd/phpcore.ini") === true) {
    $config_path = "$wd/phpcore.ini";
    $config_paths[] = $config_path;
    $ini_config = array_merge_recursive($ini_config, parse_ini_file("$wd/phpcore.ini", true));
}
unset($wd);

// =============================================================================
// version lock check
// =============================================================================
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
$GLOBALS['_CORE_INI'] = $ini_config;
$GLOBALS['_CORE'] = [
    'PATH'              => __DIR__,
    'CONFIG_FILE'       => $config_path,
    'CONFIG_FILES'      => implode(',', $config_paths),
    'DISABLE_FUNCTIONS' => $GLOBALS['_CORE_INI']['PHPCore']['disable_functions'] ?? '',
    'DISABLE_CLASSES'   => $GLOBALS['_CORE_INI']['PHPCore']['disable_classes'] ?? '',
    'INTERFACE'         => $interface,
    'FORMAT'            => ($interface === 'cli') ? 'text' : $GLOBALS['_CORE_INI']['PHPCore']['default_format'],
];
unset($config_path);
unset($ini_config);
unset($config_paths);

// =============================================================================
// PHPCore functions
// =============================================================================
include $GLOBALS['_CORE']['PATH'] . DIRECTORY_SEPARATOR . 'functions.php';

// =============================================================================
// Look for auto starts like session
// =============================================================================


// EOF /////////////////////////////////////////////////////////////////////////////////////////////
