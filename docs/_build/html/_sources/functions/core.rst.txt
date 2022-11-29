==============
Core Functions
==============

* `core_ini_get`_ - Gets the value of a PHPCore configuration directive
* `core_ini_get_all`_ - Gets all configuration options
* `core_ini_set`_ - Sets the value of a configuration directive.
* `coreinfo`_ - Get PHPCore Information

-----

.. php:function:: core_ini_get(string $directive, string $section = 'PHPCore')

   Gets the value of a PHPCore configuration directive

   Returns the value of the PHPCore configuration directive on success. If section is not passed 'the [PHPCore] section will be used.

   :param string $directive: The configuration directive name.
   :param string $section: The configuration section name.
   :return: ``string|false`` Returns the value of the configuration option as a string on success, or an empty string for null values. Returns false if the configuration option doesn't exist.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Core Functions>`

.. php:function:: core_ini_get_all(?string $section = null, ?string $sub_section = null)

   Gets all configuration options

   Returns all the registered configuration options.

   :param string $section: An optional section name. If not empty, the function returns only options specific for that section.
   :return: ``array`` Returns an associative array with directive name as the array key. Returns false and raises an E_WARNING level error if the section doesn't exist.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Core Functions>`

.. php:function:: core_ini_set()

   Sets the value of a configuration directive.

   Sets the value of the given PHPCore configuration directive. The configuration directive will keep this new value during the script's execution, and will be restored at the script's ending. This is similar to PHP ini_set() function.

   :param string $directive: The configuration directive name.
   :param string $section: The configuration section name.
   :param string $value: The new value for the option.
   :return: ``string|false`` Returns the old value on success, false on failure

   .. rst-class:: wy-text-right

      :ref:`Back to list<Core Functions>`

.. php:function:: coreinfo()

   Get PHPCore Information

   :return: List or HTML formated PHPCore information.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Core Functions>`