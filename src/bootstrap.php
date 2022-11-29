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
define('CORE_VERSION', '1.0.0-beta');

// =============================================================================
// PHPCore configurations
// =============================================================================
$phpcorewd = getcwd() . '/phpcore.ini';
$phpcorerc = getenv('PHPCORERC');
$config_paths[] = '/etc/phpcore/' . str_replace('handler', '', PHP_SAPI) . '/phpcore.ini';
if ( ! is_readable($config_paths[0])) {
    trigger_error("Missing base PHPCore configuration file. ({$config_paths[0]})", E_USER_WARNING);
}
if ( ! empty($phpcorerc) && is_readable($phpcorerc)) {
    $config_paths[] = $phpcorerc;
}
if (is_readable($phpcorewd)) {
    $config_paths[] = $phpcorewd;
}
foreach ($config_paths as $config_path) {
    if (isset($ini_config)) {
        $ini_config = array_merge_recursive($ini_config, parse_ini_file($config_path, true));
    } else {
        $ini_config = parse_ini_file($config_path, true);
    }
}

// =============================================================================
// PHPCore version lock check
// =============================================================================
if (isset($ini_config['PHPCore']['version_lock'])) {
    if ( ! str_starts_with(CORE_VERSION, $ini_config['PHPCore']['version_lock'])) {
        trigger_error("PHPCore configuration version lock mismatch.", E_USER_WARNING);
    }
}

// =============================================================================
// PHPCore globals $_CORE / $_CORE_INI
// =============================================================================
$GLOBALS['_CORE_INI'] = $ini_config;
$GLOBALS['_CORE'] = [
    'PATH'              => __DIR__,
    'CONFIG_FILES'      => implode(',', $config_paths),
    'DISABLE_FUNCTIONS' => $ini_config['PHPCore']['disable_functions'] ?? null,
    'DISABLE_CLASSES'   => $ini_config['PHPCore']['disable_classes'] ?? null,
    'INTERFACE'         => PHP_SAPI,
    'FORMAT'            => $ini_config['PHPCore']['default_format'] ?? 'json',
];

// =============================================================================
// PHPCore autoloader
// =============================================================================
spl_autoload_register(function(string $class_name) {
    if ($class_name[0] === '\\') {
        $class_name = substr($class_name, 1);
    }
    if (str_starts_with($class_name, 'PHPCore\\')) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class_name, 7));
        if (isset($GLOBALS['_CORE']['DISABLE_CLASSES'])) {
            if (in_array($file, explode(',', $GLOBALS['_CORE']['DISABLE_CLASSES']))) {
                return;
            }
        }
        include $GLOBALS['_CORE']['PATH'] . "$file.php";
    }
});

// =============================================================================
// PHPCore functions
// =============================================================================
include $GLOBALS['_CORE']['PATH'] . DIRECTORY_SEPARATOR . 'functions.php';

// =============================================================================
// Variable cleanup
// =============================================================================
unset($config_path);
unset($config_paths);
unset($ini_config);
unset($phpcorewd);
unset($phpcorerc);

// =============================================================================
// Process request
// =============================================================================
if (PHP_SAPI !== 'cli') {
    PHPCore\Request::process();
}

// =============================================================================
// Session Auto Start
// =============================================================================
if (core_ini_get('auto_start', 'Session')) {
    PHPCore\Session::getInstance();
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
