============================
Installing and Configuration
============================

.. toctree::
   :maxdepth: 2
   
   Requirements <requirements>
   Configuration Directives <directives>

...

Manual Installation
###################

...

PHP Configuration
#################

There are some PHP Configuration values that are required to ensure all of PHPCore features, classes, and functions work correctly. There are also some php.ini directives that will be forced to a value for PHPCore operation. Below are the directives that need to be set and that will be overridden to ensure PHPCore functionality.


.. code-block:: ini

   [Session]

   ; This MUST be set to "0" in the php.ini for the PHPCore sessions to function
   ; properly. If you would like the session to "Auto Start" it is recommenced
   ; to set this to "Yes" in the phpcore.ini to enable the auto starting of the
   ; session if used.
   session.auto_start = 0

   ; This directive will be overridden as stated below when using PHPCore 
   ; sessions.
   session.serialize_handler = "php_serialize"