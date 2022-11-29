=======
General
=======

* `delcookie`_ - Delete cookie
* `parse_dsn`_ - Parse DSN string
* `timetoarray`_ - Time to array
* `xml_encode`_ - Returns the XML representation of a array 

-----

.. php:function:: delcookie(string $name, string $path = '', string $domain = '')

   Delete cookie

   Defines a cookie to be sent along with the rest of the HTTP headers with an expiration time in the past therefor telling the browser the cookie has expired.

   :param string $name: The name of the cookie.
   :param string $path: The path on the server in which the cookie will be delete for. The default value is the current directory that the cookie is being deleted in.
   :param string $domain: The (sub)domain that the cookie will be deleted for.

   .. code-block:: php
      :caption: Delete cookie
      :linenos:
      :emphasize-lines: 4

      <?php

      if ($clear_search) {
        delcookie('search_text');
      }

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<General>`

.. php:function:: parse_dsn(string $dsn)

   Parse DSN string

   This function will parse a given Data Source Name (DSN) string and return an associated array of its contents.

   :param string $dsn: Data Source Name (DSN) string to parse
   :return: ``array`` Returns DSN elements as associated array.

   .. seealso::
      `PHP PDO Drivers`_ list.

   .. code-block:: php
      :caption: Get dsn parts
      :linenos:
      :emphasize-lines: 4

      <?php

      $dsn_str = 'mysql:host=localhost;dbname=my_database;charset=utf8mb4';
      $dsn_arr = parse_dsn($dsn_str);
      echo $dsn_arr['driver'];  // mysql
      echo $dsn_arr['host'];    // localhost
      echo $dsn_arr['dbname'];  // my_database
      echo $dsn_arr['charset']; // utf8mb4

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<General>`

.. php:function:: timetoarray(int $time)

   Time to array

   Takes a provide time and returns an array of time units.

   :param integer $time: Time
   :return: ``array`` Array time

   .. code-block:: php
      :caption: Time to array
      :linenos:
      :emphasize-lines: 8

      <?php
      
      var_dump(timetoarray(123456)); // [
                                     //   'secs' => int 36
                                     //   'mins' => float 17
                                     //   'hrs'  => float 10
                                     //   'days' => float 1
                                     // ];

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<General>`

.. php:function:: xml_encode(array $array, int $flags)

   Returns the XML string representation of a array 

   :param array $array: Array to be encoded as XML
   :return: ``string`` Returns a string containing the XML representation of the supplied array.

   .. code-block:: php
      :caption: Array to XML string
      :linenos:
      :emphasize-lines: 8

      <?php
      
      $data = [
          'name'  => 'John Doe',
          'email' => 'john@domain.com',
      ];

      echo xml_encode($data); // <?xml version="1.0"?>
                              // <root><name>John Doe</name><email>john@domain.com</email></root>

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<General>`

.. _PHP PDO Drivers: https://www.php.net/manual/en/pdo.drivers.php
