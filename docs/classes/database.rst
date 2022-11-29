==============
Database Class
==============

Placeholder...

----

.. php:class:: Database

.. _session::__getInstance:

.. php:method:: __getInstance(string $name = null)

   Get database class instance

   Returns the database instance for a given name name. If the database handler has not been initiated yet it will be before the instance is returned.

   If the **$name** is not provided the instance with the name Database::MAIN_INSTANCE_NAME

   :param string $name: Name of the database instance
   :returns: object Database class instance

.. php:method:: __construct(?string $name = null, string $dsn = '', string|null $usr = null, string|null $pwd = null)

   :throws Exception: if instance is not found
   :throws PDOException: if instance is not found