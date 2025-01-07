================
Config Functions
================

* `phpcore_ini_get`_ - Gets the value of a configuration option
* `phpcore_ini_get_all`_ - Gets all configuration options
* `phpcore_ini_restore`_ - Restores the value of a configuration option
* `phpcore_ini_set`_ - Sets the value of a configuration option

----

Many of the config functions below are just aliases for the methods of the `PHPCore Config Class`_.

.. seealso::
   `PHPCore Config Class`_
      The PHPCore config class.

-----

.. php:function:: phpcore_ini_get(string $option)

   Gets the value of a configuration option

   Returns the value of the configuration option on success.

   :param string $option: The configuration option name.
   :returns: ``mixed`` Returns the value of the configuration option on success. Returns null if the configuration option doesn't exist.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Functions>`

-----

.. php:function:: phpcore_ini_get_all(?string $extension = null, ?bool $details = true)

   Gets all configuration options

   Returns all the registered configuration options.

   :param ?string $extension: An optional extension name. If not null the function returns only options specific for that extension.
   :param ?bool $details: Retrieve details settings or only the current value for each setting. Default is true (retrieve details).
   :returns: ``?array`` Returns an associative array with directive name as the array key. Returns null if the extension doesn't exist.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Functions>`

-----

.. php:function:: phpcore_ini_restore(string $option)

   Restores the value of a configuration option

   Restores a given configuration option to its original value.

   :param string $option: The configuration option name.
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Functions>`

-----

.. php:function:: phpcore_ini_set(string $option, mixed $value)

   Sets the value of a configuration option

   Sets the value of the given configuration option. The configuration option will keep this new value during the script's execution, and will be restored at the script's ending.

   :param string $option: The configuration option name to set.
   :param mixed $value: The new value for the option.
   :returns: ``mixed`` Returns the old value on success, null on failure.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Config Functions>`

-----

.. _PHPCore Config Class: ../classes/config.html

