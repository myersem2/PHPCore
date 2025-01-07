============
Config Class
============

The Config class is used to store the PHPCore configuration for both the local and master configurations. It will load the master configurations from the ini files. It can be used later to view/modify the local config at runtime.

.. seealso::
   `PHPCore Config Functions`_ - PHPCore internal config functions that interface directly with this class.

Config Class Synopsis
#####################

.. code-block:: php

   final class Config {

       /* Static Methods */
       public static function get(string $option): mixed
       public static function getAll(?string $extension = null, ?bool $details = true): ?array
       public static function initialize(array $config = []): void
       public static function restore(string $option): void
       public static function set(string $option, mixed $value): mixed

   }

Config Class Table of Contents
##############################

* :ref:`Config::get<config-method-get>` - Get the value of a configuration option
* :ref:`Config::getAll<config-method-getall>` - Get all configuration options for an extension
* :ref:`Config::initialize<config-method-initialize>` - Initialize
* :ref:`Config::restore<config-method-restore>` - Restore value of a configuration option
* :ref:`Config::set<config-method-set>` - Set the value of a configuration option

Config Class Methods
####################

.. _config-method-get:
.. php:method:: get(string $option)
   :noindex:

   Get the value of a configuration option

   Returns the value of the configuration option.

   .. note::
      Returns ``null`` if configuration option does not exist.

   :param string $option: The configuration option name.
   :returns: ``mixed`` Returns the value of the configuration option on success.

   .. code-block:: php
      :caption: Get the value of a configuration option
      :linenos:
      :emphasize-lines: 6,7

      <?php
      
      use \PHPCore\Config;
      
      // Get by option
      echo Config::get('session.save_handler'); // 'files'
      var_dump(Config::get('env_ini_allowed')); // true
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Class Table of Contents>`

-----

.. _config-method-getall:
.. php:method:: getAll(?string $extension = null, ?bool $details = true)
   :noindex:

   Get all configuration options for an extension

   Returns all the registered configuration options.

   .. note::
      Returns ``null`` if configuration extension does not exist.

   :param ?string $extension: An optional extension name. If not null the function returns only options specific for that extension. Default null (retrieve all options).
   :param ?bool $details: Retrieve details settings or only the current value for each setting. Default is true (retrieve details).
   :returns: ``?array`` Returns an associative array with directive name as the array key.

   .. code-block:: php
      :caption: Get all configuration options for an extension
      :linenos:
      :emphasize-lines: 6,16

      <?php
      
      use \PHPCore\Config;
      
      // Get all by option
      var_dump(Config::getAll('session', false));
      // array(19) {
      //   ["session.enabled"]=>
      //   bool(true)
      //   ["session.save_handler"]=>
      //   string(5) "files"
      //   ...
      //)
      
      // Get all by option
      var_dump(Config::getAll('session'));
      // array(19) {
      //   ["session.enabled"]=>
      //   array(2) {
      //     ["local"]=>
      //     bool(true)
      //     ["master"]=>
      //     NULL
      //   }
      //   ["session.save_handler"]=>
      //   array(2) {
      //     ["local"]=>
      //     string(5) "files"
      //     ["master"]=>
      //     NULL
      //   }
      //   ...
      //)
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Class Table of Contents>`

-----

.. _config-method-initialize:
.. php:method:: initialize(array $config = [])
   :noindex:

   Initialize

   This method is used to initialize the PHPCore config and is called in the bootstrap file. It can be used to pass the runtine configuration via setting the ``$phpcore_runtime_config`` array **BEFORE** the bootloader is loaded.

   :param array $config: Runtime configuration.
   :returns: ``void`` 
   :throws: ``ConfigException`` Config already initialized.
   :throws: ``ConfigException`` Config could not be loaded.
   :throws: ``ConfigException`` Option type not set.
   :throws: ``ConfigException`` Option declared as string, but is array.
   :throws: ``ConfigException`` Option type unknown.

   .. code-block:: php
      :caption: Using PHPCore runtime config
      :linenos:
      :emphasize-lines: 6

      <?php
      
      // [phpcore.ini]
      // session.auto_start = No
      
      $phpcore_runtime_config = [ 'session.auto_start' => 'Yes' ];
      include getenv('PHPCORE_BOOTSTRAP');
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Class Table of Contents>`

-----

.. _config-method-restore:
.. php:method:: restore(string $option)
   :noindex:

   Restore value of a configuration option

   Restores a given configuration option to the master value that was declared in the phpcore.ini files.

   :param string $option: The configuration option name.
   :returns: ``void`` 

   .. code-block:: php
      :caption: Restore value of a configuration option
      :linenos:
      :emphasize-lines: 13-14

      <?php
      
      // [phpcore.ini]
      // session.auto_start = No
      // response.powered_by = "PHPCore"
      
      $phpcore_runtime_config = [ 'session.auto_start' => 'Yes' ];
      include getenv('PHPCORE_BOOTSTRAP');
      
      Config::set('response.powered_by', 'MyApp')
      echo Config::get('response.powered_by'); // 'MyApp'
      
      Config::restore('session.auto_start');
      Config::restore('response.powered_by');
      
      echo Config::get('session.auto_start'); // false
      echo Config::get('response.powered_by'); // 'PHPCore'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Class Table of Contents>`

-----

.. _config-method-set:
.. php:method:: set(string $option, mixed $value)
   :noindex:

   Set the value of a configuration option

   Sets the value of a given configuration option and will return the original previous value on success and ``null`` on failure.

   .. note::
      Will return ``null`` if the option cannot be cahnged durring runtime.

   :param string $option: The configuration option name to set.
   :param mixed $value: The new value for the option.
   :returns: ``mixed`` Returns the old value on success, null on failure.
   :throws: ``ConfigException`` Option type not set.
   :throws: ``ConfigException`` Option declared as string, but is array.
   :throws: ``ConfigException`` Option type unknown.

   .. code-block:: php
      :caption: Set the value of a configuration option
      :linenos:
      :emphasize-lines: 9,12

      <?php
      
      // [phpcore.ini]
      // version_lock = "1.0"
      // response.powered_by = "PHPCore"
      
      include getenv('PHPCORE_BOOTSTRAP');
      
      echo Config::set('response.powered_by', 'MyApp'); // 'PHPCore'
      echo Config::get('response.powered_by'); // 'MyApp'
      
      var_dump(Config::set('version_lock', '1.1')); // null
      echo Config::get('version_lock'); // '1.0'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Class Table of Contents>`

.. _`PHPCore Config Functions`: ../functions/config.html
