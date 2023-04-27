<?php declare(strict_types=1);
/**
 * PHPCore - Bootstrap
 *
 * @author Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2023, PHPCore
 * @link https://manual.phpcore.org/bootstrap.html
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

// @codeCoverageIgnoreStart

// PHPCore defines
define('CORE_MAJOR_VERSION', 1);
define('CORE_MINOR_VERSION', 0);
define('CORE_RELEASE_VERSION', 0);
define('CORE_VERSION_ID', 10000);
define('CORE_EXTRA_VERSION', '-beta');
define('CORE_VERSION', '1.0.0-beta');

// PHP version check
if (version_compare('8.1.0', PHP_VERSION, '>')) {
    throw new Exception('This version of PHPCore requires PHP >= 8.1.');
}

// PHPCore config
require __DIR__ . DIRECTORY_SEPARATOR . 'Config.php';
Config::initialize($phpcore_runtime_config ?? []);

// Version lock
if ( ! empty(Config::get('version_lock'))) {
    if ( ! str_starts_with(CORE_VERSION, Config::get('version_lock'))) {
        trigger_error('PHPCore configuration version lock mismatch.', E_USER_WARNING);
    }
}

// PHPCore functions
require __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

// PHPCore auto-loader
spl_autoload_register(function(string $class_name) {
    preg_match('/^(\\\\PHPCore|PHPCore)\\\\(.*)/', $class_name, $matches);
    if (count($matches) == 3) {
        if ( ! in_array($matches[2], Config::get('disable_classes'))) {
            $relative_path = str_replace('\\', DIRECTORY_SEPARATOR, $matches[2]);
            require Config::get('core_path') . DIRECTORY_SEPARATOR . "$relative_path.php";
        }
    }
});

// Include file
if ( ! empty(Config::get('include_file'))) {
    require Config::get('include_file');
}

// Session auto-start
if (Config::get('session.auto_start')) {
    //PHPCore\Session::getInstance();
}

// Response auto-send
if (Config::get('response.auto_send')) {
    //register_shutdown_function('Response::send')
}

// @codeCoverageIgnoreEnd

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
