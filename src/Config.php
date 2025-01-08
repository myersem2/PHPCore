<?php declare(strict_types=1);
/**
 * PHPCore - Config
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-05
 */

namespace PHPCore;

// -----------------------------------------------------------------------------

/**
 * Config Class
 *
 * The Config class is used to store the PHPCore configuration for both the
 * local and master configurations. It will load the master configurations
 * from the ini files. It can be used later to view/modify the local config at
 * runtime.
 *
 * @seealso `PHPCore Config Functions`_ - PHPCore internal config functions that
 *          interface directly with this class.
 *
 * @refence `PHPCore Config Functions`: ../functions/config.html
 */
#[Test('tests/ConfigTest.php')]
#[Documentation('docs/classes/config.rst')]
final class Config
{
    /**
     * PHPCore ini config options.
     *
     * @ignore
     * @const array
     */
    public const CONFIG_OPTIONS = [
        'core_path' => [
            'type'      => 'string',
            'default'   => '',
            'access'    => 'BEC',
        ],
        'version_lock' => [
            'type'      => 'string',
            'default'   => '1',
            'access'    => 'BEC',
        ],
        'disable_functions' => [
            'type'      => 'array',
            'default'   => [],
            'access'    => 'BEC',
        ],
        'disable_classes' => [
            'type'      => 'array',
            'default'   => [],
            'access'    => 'BEC',
        ],
        'include_file' => [
            'type'      => 'string',
            'default'   => '',
            'access'    => 'BEC',
        ],
        'env_ini_allowed' => [
            'type'      => 'bool',
            'default'   => '',
            'access'    => 'B',
        ],
        'cwd_ini_allowed' => [
            'type'      => 'bool',
            'default'   => '',
            'access'    => 'BE',
        ],
        'runtime_config_allowed' => [
            'type'      => 'bool',
            'default'   => '',
            'access'    => 'BEC',
        ],
    ];

    // ---------------------------------------------------------------------

    /**
     * Local config
     *
     * The local config in use.
     *
     * @ignore
     * @prop array
     */
    private static array $Local;

    /**
     * Master config set by the PHPCore ini files.
     *
     * @ignore
     * @prop array
     */
    private static array $Master;

    /**
     * Current PHPCore ini options.
     *
     * @ignore
     * @prop array
     */
    private static array $Options;

    /**
     * Parsed Configs
     *
     * This contains the ini files that were used to build the current config.
     *
     * @ignore
     * @prop array
     */
    private static array $ParsedConfigs;

    // ---------------------------------------------------------------------

    /**
     * Clear
     *
     * This method is used to clear or reset the entire config. This should not
     * normally be required and requires the ``CORE_CONFIG_CLEAR_ENABLED``
     * defined to set to ``true`` other wise an Exception will be thrown.
     *
     * @warning This should **NOT** be used in a production environment.
     *
     * @ignore
     * @codeCoverageIgnore
     * @return void
     * @throws Exception If config cannot be cleared.
     */
    public static function clear(): void
    {
        if (
            ! defined('CORE_CONFIG_CLEAR_ENABLED') ||
            empty(CORE_CONFIG_CLEAR_ENABLED)
        ) {
            throw new ConfigException(
                'PHPCore config cannot be cleared.'
            );
        }
        self::$Local = [];
        self::$Master = [];
        self::$ParsedConfigs = [];
    }

    /**
     * Get the value of a configuration option
     *
     * Returns the value of the configuration option.
     *
     * @note Returns ``null`` if configuration option does not exist.
     *
     * @example Get the value of a configuration option
     * <code linenos="true" emphasize-lines="6,7">
     *
     * use \PHPCore\Config;
     *
     * // Get by option
     * echo Config::get('session.save_handler'); // 'files'
     * var_dump(Config::get('env_ini_allowed')); // true
     *
     * </code>
     *
     * @param string $option The configuration option name.
     * @return mixed Returns the value of the configuration option on success.
     */
    public static function get(string $option): mixed
    {
        return self::$Local[$option] ?? null;
    }

    /**
     * Get all configuration options for an extension
     *
     * Returns all the registered configuration options.
     *
     * @note Returns ``null`` if configuration extension does not exist.
     *
     * @example Get all configuration options for an extension
     * <code linenos="true" emphasize-lines="6,16">
     *
     * use \PHPCore\Config;
     *
     * // Get all by option
     * var_dump(Config::getAll('session', false));
     * // array(19) {
     * //   ["session.enabled"]=>
     * //   bool(true)
     * //   ["session.save_handler"]=>
     * //   string(5) "files"
     * //   ...
     * //)
     *
     * // Get all by option
     * var_dump(Config::getAll('session'));
     * // array(19) {
     * //   ["session.enabled"]=>
     * //   array(2) {
     * //     ["local"]=>
     * //     bool(true)
     * //     ["master"]=>
     * //     NULL
     * //   }
     * //   ["session.save_handler"]=>
     * //   array(2) {
     * //     ["local"]=>
     * //     string(5) "files"
     * //     ["master"]=>
     * //     NULL
     * //   }
     * //   ...
     * //)
     *
     * </code>
     *
     * @param ?string $extension An optional extension name. If not null the
     *                           function returns only options specific for that
     *                           extension. Default null (retrieve all options).
     * @param ?bool $details Retrieve details settings or only the current value
     *                       for each setting. Default is true (retrieve
     *                       details).
     * @return ?array Returns an associative array with directive name as the
     *                array key.
     */
    public static function getAll(
        ?string $extension = null,
        ?bool $details = true
    ): ?array
    {
        $filtered = array_filter(
            self::$Local,
            fn($k) => str_starts_with($k, "$extension.") || $extension === null,
            ARRAY_FILTER_USE_KEY
        );

        if (empty($filtered)) {
            return null;
        }

        if ( ! $details) {
            return $filtered;
        }

        $detailed = [];
        foreach ($filtered as $option=>$local) {
            $detailed[$option] = [
                'local'  => $local,
                'master' => self::$Master[$option] ?? null,
            ];
        }

        return $detailed;
    }

    /**
     * Initialize
     *
     * This method is used to initialize the PHPCore config and is called in the
     * bootstrap file. It can be used to pass the runtine configuration via
     * setting the ``$phpcore_runtime_config`` array **BEFORE** the bootloader
     * is loaded.
     *
     * @example Using PHPCore runtime config
     * <code linenos="true" emphasize-lines="6">
     *
     * // [phpcore.ini]
     * // session.auto_start = No
     *
     * $phpcore_runtime_config = [ 'session.auto_start' => 'Yes' ];
     * include getenv('PHPCORE_BOOTSTRAP');
     *
     * </code>
     *
     * @param array $config Runtime configuration.
     * @return void
     * @throws ConfigException Config already initialized.
     * @throws ConfigException Config could not be loaded.
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    public static function initialize(array $config = []): void
    {
        if ( ! empty(self::$Local)) {
            throw new ConfigException(
                'PHPCore config already initialized.'
            );
        }

        self::$Local['core_path'] = __DIR__;

        self::$Options = self::CONFIG_OPTIONS;
        foreach (array_keys(self::$Options) as $option) {
            self::setDefault($option);
        }

        self::initializeBaseConfig();
        self::initializeEnvironmentConfig();
        self::initializeDirectoryConfig();

        self::$Master = self::$Local;

        self::initializeRuntimeConfig($config);
    }

    /**
     * Restore value of a configuration option
     *
     * Restores a given configuration option to the master value that was
     * declared in the phpcore.ini files.
     *
     * @example Restore value of a configuration option
     * <code linenos="true" emphasize-lines="13-14">
     *
     * // [phpcore.ini]
     * // session.auto_start = No
     * // response.powered_by = "PHPCore"
     *
     * $phpcore_runtime_config = [ 'session.auto_start' => 'Yes' ];
     * include getenv('PHPCORE_BOOTSTRAP');
     *
     * Config::set('response.powered_by', 'MyApp')
     * echo Config::get('response.powered_by'); // 'MyApp'
     *
     * Config::restore('session.auto_start');
     * Config::restore('response.powered_by');
     *
     * echo Config::get('session.auto_start'); // false
     * echo Config::get('response.powered_by'); // 'PHPCore'
     *
     * </code>
     *
     * @param string $option The configuration option name.
     * @return void
     */
    public static function restore(string $option): void
    {
        if (isset(self::$Master[$option])) {
            self::$Local[$option] = self::$Master[$option];
        }
    }

    /**
     * Set the value of a configuration option
     *
     * Sets the value of a given configuration option and will return the
     * original previous value on success and ``null`` on failure.
     *
     * @note Will return ``null`` if the option cannot be cahnged durring
     *       runtime.
     *
     * @example Set the value of a configuration option
     * <code linenos="true" emphasize-lines="9,12">
     *
     * // [phpcore.ini]
     * // version_lock = "1.0"
     * // response.powered_by = "PHPCore"
     *
     * include getenv('PHPCORE_BOOTSTRAP');
     *
     * echo Config::set('response.powered_by', 'MyApp'); // 'PHPCore'
     * echo Config::get('response.powered_by'); // 'MyApp'
     *
     * var_dump(Config::set('version_lock', '1.1')); // null
     * echo Config::get('version_lock'); // '1.0'
     *
     * </code>
     *
     * @param string $option The configuration option name to set.
     * @param mixed $value The new value for the option.
     * @return mixed Returns the old value on success, null on failure.
     * @throw ConfigException Unknown config option
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    public static function set(string $option, mixed $value): mixed
    {
        if ( ! isset(self::$Local[$option])) {
            return null;
        }

        if (is_null($value)) {
            return null;
        }

        if ( ! self::canBeSet($option, 'runtime')) {
            return null;
        }

        $old_value = self::$Local[$option];
        self::$Local[$option] = self::typeMask($option, $value);
        return $old_value;
    }

    // ---------------------------------------------------------------------

    /**
     * Can options be set
     *
     * This method is used to check if an option can be set in the current
     * config environment call. ('[B]ase', '[C]wd', '[E]nv', '[R]untime')
     *
     * @ignore
     * @param string $option The configuration option name.
     * @param string $type Tyoe of config environment.
     * @return bool Option can be set
     * @throw ConfigException Unknown config option
     */
    private static function canBeSet(string $option, string $type): bool
    {
        if (empty(self::$Options[$option])) {
            throw new ConfigException(
                "Option `$option` not set in phpcore.ini."
            );
        }

        $access = self::$Options[$option]['access'] ?? 'BECR';

        return (strpos($access, ucfirst($type)[0]) !== false);
    }

    /**
     * Initialize base environment
     *
     * This method is used to initialize the PHPCore base config.
     *
     * @ignore
     * @return void
     * @throws ConfigException Config could not be loaded.
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function initializeBaseConfig(): void
    {
        $php_sapi = str_replace('handler', '', PHP_SAPI);
        self::processIni("/etc/phpcore/$php_sapi/phpcore.ini", 'base');
    }

    /**
     * Initialize current working directory (cwd) config
     *
     * This method is used to initialize the PHPCore working directory config.
     *
     * @ignore
     * @return void
     * @throws ConfigException Config could not be loaded.
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function initializeDirectoryConfig(): void
    {
        if (self::$Local['cwd_ini_allowed']) {
            $cwd_ini_path = getcwd() . DIRECTORY_SEPARATOR . 'phpcore.ini';
            if (file_exists($cwd_ini_path)) {
                self::processIni($cwd_ini_path, 'cwd');
            }
        }
    }

    /**
     * Initialize environment (env) config
     *
     * This method is used to initialize the PHPCore environment config.
     *
     * @ignore
     * @return void
     * @throws ConfigException Config could not be loaded.
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function initializeEnvironmentConfig(): void
    {
        if (self::$Local['env_ini_allowed']) {
            $env_ini_path = getenv('PHPCORERC');
            if ($env_ini_path !== false) {
                self::processIni($env_ini_path, 'env');
            }
        }
    }

    /**
     * Initialize runtime config
     *
     * This method is used to initialize the PHPCore runtime config.
     *
     * @ignore
     * @param array $config Runtime configuration
     * @return void
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function initializeRuntimeConfig(array $config = []): void
    {
        if (self::$Local['runtime_config_allowed']) {
            if ( ! empty($config)) {
                self::processOptions(array_keys($config));

                $new_config = [];
                foreach ($config as $option => $value) {
                    if ( ! self::canBeSet($option, 'runtime')) {
                        continue;
                    }
                    if ( ! is_array($value)) {
                        $value = strval($value);
                    }
                    $new_config[$option] = self::typeMask($option, $value);
                }

                self::$Local = array_merge(self::$Local, $new_config);
            }
        }
    }

    /**
     * Process configuration ini file
     *
     * This method is used to process a given ``$path`` and add it to the
     * PHPCore config.
     *
     * @ignore
     * @param string $path Config INI Path
     * @param string $source Source where the new config is coming from
     * @return void
     * @throws ConfigException Config could not be loaded.
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function processIni(string $path, string $source): void
    {
        $ini_array = @parse_ini_file($path);
        if ($ini_array === false) {
            throw new ConfigException(
                "PHPCore $source config could not be loaded."
            );
        }
        self::$ParsedConfigs[] = $path;

        self::processOptions(array_keys($ini_array));

        $new_config = [];
        foreach ($ini_array as $option => $value) {
            if ( ! self::canBeSet($option, $source)) {
                continue;
            }
            $new_config[$option] = self::typeMask($option, $value);
        }

        self::$Local = array_merge(self::$Local, $new_config);
    }

    /**
     * Process options
     *
     * This method is used to process a options to load the extentions
     * ``CONFIG_OPTIONS``. This will also set the option's default value.
     *
     * @ignore
     * @param array $options COnfig options
     * @return void
     */
    private static function processOptions(array $options): void
    {
        foreach ($options as $option) {
            if (strpos($option, '.') !== false) {
                list($directive, $sub_option) = explode('.', $option);
                $module = ucfirst($directive);
                $module_options = constant("PHPCore\\$module::CONFIG_OPTIONS");
                self::$Options = array_merge(
                    self::$Options,
                    $module_options
                );
                foreach (array_keys($module_options) as $module_option) {
                    self::setDefault($module_option);
                }
            }
        }
    }

    /**
     * Set default option's value
     *
     * This method is used to set the option's default value.
     *
     * @ignore
     * @param string $option The configuration option name.
     * @return void
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function setDefault(string $option): void
    {
        if ( ! isset(self::$Local[$option])) {
            $value = self::typeMask(
                $option,
                self::$Options[$option]['default']
            );
            self::$Local[$option] = $value;
        }
    }

    /**
     * Type mask an options's value
     *
     * This method is used to set the proper datatype for a given ``$option``
     * and its ``$value``.
     *
     * @ignore
     * @param string $option The configuration option name.
     * @param mixed $value The string value of the option.
     * @return mixed The formatted data type
     * @throws ConfigException Option type not set.
     * @throws ConfigException Option declared as string, but is array.
     * @throws ConfigException Option type unknown.
     */
    private static function typeMask(string $option, mixed $value): mixed
    {
        if (
            empty(self::$Options[$option]['type']) ||
            ! is_string(self::$Options[$option]['type'])
        ) {
            throw new ConfigException(
                "Option `$option` type not set in phpcore.ini."
            );
        }

        $type = self::$Options[$option]['type'];

        switch ($type) {
            case 'bool':
                return boolval($value);

            case 'int':
                return intval($value);

            case 'string':
                if (is_array($value)) {
                    throw new ConfigException(
                        "Option `$option` declared as string, array given."
                    );
                }
                return strval($value);

            case 'array':
                return $value;

            default:
                throw new ConfigException(
                    "Config option `$option` unknown type $type."
                );
        }
    }
}

// -----------------------------------------------------------------------------

/**
 * Config Exception Class
 *
 * The Exception class is used to throw exceptions in the Config class.
 *
 * @codeCoverageIgnore
 */
final class ConfigException extends \Exception
{
    /**
     * To string
     *
     * This method is get the description of the exception.
     *
     * @ignore
     * @return string Exception description
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
