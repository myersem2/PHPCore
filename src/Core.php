<?php declare(strict_types=1);
/**
 * PHPCore - Core Trait
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

use \Exception;

// -------------------------------------------------------------------------------------------------

/**
 * Core Trait
 *
 * This class is used to use to the needed magic methods to the PHPCore classes.
 */
trait Core
{
    /**
     * The name for the default instance for this class
     * The following for reference only and is required to be set in the class
     * that is using this traits.
     */
    //public const DEFAULT_INSTANCE_NAME = 'main';

    // -----------------------------------------------------------------------------------------

    /**
     * Has this class been initialized
     *
     * @var boolean
     */
    protected static $Initialized = false;

    /**
     * Instance that have been created
     *
     * @var array
     */
    protected static $Instances = [];

    // -----------------------------------------------------------------------------------------

    /**
     * Database config
     *
     * @var array   
     */
    protected array $Config = [];

    /**
     * This PDO handler
     *
     * @var object   
     */
    protected ?object $Handler = null;

    // -----------------------------------------------------------------------------------------

    /**
     * Destroy all class instances
     *
     * Destructs all class instance that have been constructed.
     *
     * @return void
     */
    public static function destroyAllInstances(): void
    {
        static::$Instances = [];
    }

    /**
     * Destroy class instance
     *
     * Destructs class instance that has been constructed for a specified name.
     *
     * @param string $name Name of instance, if not specified, ``static::DEFAULT_INSTANCE_NAME`` will
     *                     be used.
     * @return void
     */
    public static function destroyInstance(?string $name = null): void
    {
        $name = $name ?? static::DEFAULT_INSTANCE_NAME;
        unset(static::$Instances[$name]);
    }

    /**
     * Get class instance
     *
     * Returns the class instance for a specified name. This method will also invoke the
     * ``__construct()`` method with the specified name as the first parameter if the specified
     * instance has not been constructed.
     *
     * @param string $name Name of instance, if not specified, ``static::DEFAULT_INSTANCE_NAME`` will
     *                     be used.
     * @return object Class instance
     */
    public static function &getInstance(?string $name = null): object
    {
        $name = $name ?? static::DEFAULT_INSTANCE_NAME;
        if (empty(static::$Instances[$name])) {
            static::$Instances[$name] = new static($name);
        }
        return static::$Instances[$name];
    }

    /**
     * Is class instance constructed
     *
     * Checks if class instance for a specified name has been constructed.
     *
     * @return boolean
     */
    public static function isConstructed(?string $name = null): bool
    {
      $name = $name ?? static::DEFAULT_INSTANCE_NAME;
      return isset(static::$Instances[$name]);
    }

    /**
     * Initialize class
     *
     * Used to preform routines that are required only once during runtime.
     *
     * @return void
     */
    public static function initialize(): void
    {
      if (static::$Initialized) {
        return;
      }
      static::$Initialized = true;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
