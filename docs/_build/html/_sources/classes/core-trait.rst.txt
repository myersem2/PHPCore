==========
Core Trait
==========

Trait synopsis
##############

.. code-block:: php

   trait CoreClass {

      /**
       * Reference Only
       *
       * The following are required to be set in class using this trait
       *
      const DEFAULT_INSTANCE_NAME = 'main';
      protected static $Initialized = false;
      protected static $Instances = [];
      */

      /* Static Methods */
      public static function destroyAllInstances(): void
      public static function destroyInstance(?string $name = null): void
      public static function &getInstance(?string $name = null): object
      public static function isConstructed(?string $name = null): bool
      public static function initialize(): void

      /* Properties */
      protected $Config = [];
      protected $Handler = null;

   }

Core Trait methods
##################

.. php:trait:: CoreTrait

.. php:staticmethod:: __destroyAllInstances( )

   Destroy all class instances

   Destructs all class instance that have been constructed.

.. php:staticmethod:: __destroyInstance(?string $name = null)

   Destroy class instance

   Destructs class instance that has been constructed for a specified name.

   :param string $name: Name of instance, if not specified, ``static::DEFAULT_INSTANCE_NAME`` will be used.

.. php:staticmethod:: getInstance(?string $name = null)

   Get session class instance

   Returns the class instance for a specified name. This method will also invoke the ``__construct()`` method with the specified name as the first parameter if the specified instance has not been constructed.

   :param string $name: Name of instance, if not specified, ``static::DEFAULT_INSTANCE_NAME`` will be used.
   :returns: ``object`` Session class instance

.. php:staticmethod:: __isConstructed(?string $name = null)

   Is class instance constructed

   Checks if class instance for a specified name has been constructed.

.. php:staticmethod:: __initialize( )

   Initialize class

   Used to preform routines that are required only once during runtime.
