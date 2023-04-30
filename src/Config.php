<?php declare(strict_types=1);
/**
 * PHPCore - Request
 *
 * @author Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2023, PHPCore
 * @link https://manual.phpcore.org/classes/config.html
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Config Class
 *
 * @todo document
 */
#[Test('../tests/ConfigTest.php')]
#[Documentation('../docs/classes/config.rst')]
final class Config
{
    /**
     * Base INI
     *
     * This is the contents of the required base phpcore.ini directives normally located in
     * `/etc/phpcore/{PHP_SAPI}/phpcore.ini`
     *
     * @note This file **MUST** exist
     *
     * @ignore
     * @var array
     */
    private static array $BaseIni;

    /**
     * Environmental INI
     *
     * This is the contents of the *optional* environment phpcore.ini directives which location is
     * declared using the `PHPCORERC` environment variable.
     *
     * @ignore
     * @var array
     */
    private static array $EnvIni;

    /**
     * Directory INI
     *
     * This is the contents of the *optional* environment phpcore.ini directives normally located in
     * `/etc/phpcore/{PHP_SAPI}/phpcore.ini`
     *
     * @ignore
     * @var array
     */
    private static array $DirIni;

    /**
     * Runtime INI
     *
     * This is the contents of the *optional* runtime phpcore.ini directives that were passed to the
     * boostrap.
     *
     * @ignore
     * @var array
     */
    private static array $RuntimeIni;

    /**
     * Current INI
     *
     * This is the contents of the current phpcore.ini directives that have been set.
     *
     * @ignore
     * @var array
     */
    private static array $CurrentIni;

    /**
     * Used Configs
     *
     * This contains the configs that were used to build the current configuration.
     *
     * @ignore
     * @var array
     */
    private static array $UsedConfigs;

    /**
     * Default INI
     *
     * This is the contents of ALL the default phpcore.ini directives to be set if not set in other
     * phpcore.ini files.
     *
     * @ignore
     * @var array
     */
    private static array $DefaultIni = [
        'PHPCore' => [
            // Set and locked in this config
            'core_path' => '',

            // Standard directives
            'version_lock' => '1',
            'disable_functions' => [],
            'disable_classes' => [],
            'include_file' => '',
            'env_ini_allowed' => false,
            'cwd_ini_allowed' => false,
            'runtime_config_allowed' => false,
        ]
    ];

    /**
     * Locked Directives
     *
     * This contains the directives that are locked within the different configs.
     *
     * @ignore
     * @var array
     */
    private static array $LockedDirectives = [
        'base' => [
            'core_path',
        ],
        'env' => [
            'core_path', 'env_ini_allowed',
        ],
        'cwd' => [
            'core_path', 'env_ini_allowed', 'cwd_ini_allowed',
        ],
        'runtime' => [
            'core_path', 'version_lock', 'disable_functions', 'disable_classes', 'include_file',
            'env_ini_allowed', 'cwd_ini_allowed', 'runtime_config_allowed',
        ],
    ];

    /**
     * Directive Types
     *
     * This contains the directives that are locked within the different configs.
     *
     * @ignore
     * @var array
     */
    private static array $DirectiveTypes = [
        'booleans' => [
            'env_ini_allowed', 'cwd_ini_allowed', 'runtime_config_allowed',
        ],
        'csv' => [
            'disable_functions', 'disable_classes',
        ],
    ];

    // ---------------------------------------------------------------------

    /**
     * Initialize
     *
     * This method is used to initialize the PHPCore config and is called in the bootstrap file. It
     * will trigger a warning if executed after it has already been initialized.
     *
     * @ignore
     *
     * @param array $runtime_config Runtime configuration
     */
    public static function initialize(array $runtime_config = []): void
    {
        // TODO : add tracking for where the class was initialized
        //$bt = debug_backtrace();
        //$caller = array_shift($bt);
  
        if (isset(self::$BaseIni)) {
            trigger_error('PHPCore configuration can only be initialized once.', E_USER_WARNING);
            return;
        }

        self::$DefaultIni = self::$DefaultIni;
        self::$CurrentIni = self::$DefaultIni;

        self::$CurrentIni['PHPCore']['core_path'] = getcwd();

        $php_sapi = str_replace('handler', '', PHP_SAPI);
        $base_ini_path = "/etc/phpcore/$php_sapi/phpcore.ini";
        $base_ini = @parse_ini_file($base_ini_path, true);
        if ($base_ini === false) {
            throw new Exception('The base PHPCore configuration file could not be loaded.');
        }
        self::mergeConfig($base_ini, 'base');
        self::$BaseIni = $base_ini;
        self::$UsedConfigs[] = $base_ini_path;

        if (self::$CurrentIni['PHPCore']['env_ini_allowed']) {
            $env_ini_path = getenv('PHPCORERC');
            $env_ini = @parse_ini_file($env_ini_path, true);
            if ($env_ini !== false) {
                self::mergeConfig($env_ini, 'env');
                self::$EnvIni = $env_ini;
                self::$UsedConfigs[] = $env_ini_path;
            } elseif(file_exists($env_ini_path)) {
                throw new Exception('The environmental PHPCore configuration file could not be loaded.');
            }
        }

        if (self::$CurrentIni['PHPCore']['cwd_ini_allowed']) {
            $cwd_ini_path = getcwd() . DIRECTORY_SEPARATOR . 'phpcore.ini';
            $cwd_ini = @parse_ini_file($cwd_ini_path, true);
            if ($cwd_ini !== false) {
                self::mergeConfig($cwd_ini, 'cwd');
                self::$DirIni = $cwd_ini;
                self::$UsedConfigs[] = $cwd_ini_path;
            } elseif(file_exists($cwd_ini_path)) {
                throw new Exception('The directory PHPCore configuration file could not be loaded.');
            }
        }

        if (self::$CurrentIni['PHPCore']['runtime_config_allowed']) {
            if ( ! empty($runtime_config)) {
                self::mergeConfig($runtime_config, 'runtime');
                self::$RuntimeIni = $runtime_config;
            }
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Get PHPCore config directive
     *
     * This method is used to get a directive from the current PHPCore config.
     *
     * @note Returns null if directive is not found
     *
     * @param string $retrieve_directive Directive to retrieve
     * @return mixed The value of the directive
     */
    public static function get(string $retrieve_directive): mixed
    {
        foreach (self::$CurrentIni as $directives) {
            foreach ($directives as $directive => $value) {
                if ($retrieve_directive == $directive) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Get all PHPCore config directive for a section
     *
     * This method is used to get all the directives from section of the current
     * PHPCore config.
     *
     * @note Returns empty array if directive is not found
     *
     * @param string $retrieve_section Section to retrieve
     * @return array An array of the directives for a give section
     */
    public static function getAll(string $retrieve_section): array
    {
        foreach (self::$CurrentIni as $section => $directives) {
            if ($retrieve_section == $section) {
                return $directives;
            }
        }

        return [];
    }

    /**
     * Set PHPCore config directive
     *
     * This method is used to set a directive to the current PHPCore runtime
     * config.
     *
     * @note Returns the old value on success, null on failure
     *
     * @param string $set_directive Directive to set
     * @param mixed $new_value New value
     */
    public static function set(string $set_directive, mixed $new_value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (in_array($set_directive, self::$LockedDirectives['runtime'])) {
            return null;
        }

        foreach (self::$CurrentIni as $section => $directives) {
            foreach ($directives as $directive => $value) {
                if ($set_directive == $directive) {
                    $old_value = self::$CurrentIni[$section][$directive];
                    $runtime_config = [
                        $section => [ $directive => $new_value ]
                    ];
                    self::mergeConfig($runtime_config, 'runtime');
                    self::$RuntimeIni[$section][$directive] = self::$CurrentIni[$section][$directive];
                    return $old_value;
                }
            }
        }

        return null;
    }

    // ---------------------------------------------------------------------

    /**
     * Merge Config
     *
     * This method is used to merge configs into the self::$Current
     *
     * @ignore
     *
     * @param array $config Config to merge into Current INI
     * @param string $source Source where the new config is coming from
     */
    private static function mergeConfig(array $config, string $source): void
    {
        static $booleans = [
            'env_ini_allowed', 'cwd_ini_allowed', 'runtime_config_allowed'
        ];
        static $csv = [
            'disable_functions', 'disable_classes'
        ];
        foreach ($config as $section => $directives) {
            foreach ($directives as $directive => $value) {
                if (in_array($directive, self::$LockedDirectives[$source])) {
                    continue;
                }
                self::$CurrentIni[$section][$directive] = match(true) {
                    in_array($directive, self::$DirectiveTypes['booleans']) => ! empty($value),
                    in_array($directive, self::$DirectiveTypes['csv']) => empty($value) ? [] : explode(',', $value),
                    default => $value,
                };
            }
        }
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
