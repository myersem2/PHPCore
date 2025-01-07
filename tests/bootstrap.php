<?php declare(strict_types=1);
/**
 * PHPCore - Test Bootstrap
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-04
 */

namespace PHPCore;

// -----------------------------------------------------------------------------

// @codeCoverageIgnoreStart

$ds = DIRECTORY_SEPARATOR;

// TODO: RBF - For testing ONLY
define('CORE_CONFIG_CLEAR_ENABLED', true);

// PHPCore defines
define('CORE_MAJOR_VERSION',   1);
define('CORE_MINOR_VERSION',   0);
define('CORE_RELEASE_VERSION', 0);
define('CORE_VERSION_ID',      10000);
define('CORE_EXTRA_VERSION',   '-beta');
define('CORE_VERSION',         '1.0.0-beta');
define('CORE_PATH',            realpath(__DIR__ . "{$ds}..{$ds}src") . $ds);

// PHP version check
if (version_compare('8.1.0', PHP_VERSION, '>')) {
    throw new Exception('This version of PHPCore requires PHP >= 8.1.');
}

// PHPCore auto-loader
spl_autoload_register(function(string $class_name) {
    $ds = DIRECTORY_SEPARATOR;
    preg_match("/^(\\\\PHPCore|PHPCore)\\\\(.*)/", $class_name, $matches);
    if (count($matches) === 3) {
        if ( ! in_array($matches[2], Config::get('disable_classes'))) {
            $relative_path = str_replace("\\", $ds, $matches[2]);
            require CORE_PATH . "$relative_path.php";
        }
    }
});

// initialize PHPCore config
require CORE_PATH . 'Config.php';
Config::initialize();

// Check for version lock
if ( ! empty(Config::get('version_lock'))) {
    if ( ! str_starts_with(CORE_VERSION, Config::get('version_lock'))) {
        trigger_error(
            'PHPCore configuration version lock mismatch.',
            E_USER_WARNING
        );
    }
}

// PHPCore functions
require CORE_PATH . 'functions.php';

// @codeCoverageIgnoreEnd

// EOF /////////////////////////////////////////////////////////////////////////
