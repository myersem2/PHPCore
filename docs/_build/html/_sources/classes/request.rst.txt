=============
Request Class
=============

The Request class is used to simplify working with data sent via http request.

.. seealso::
   `PHPCore Request Functions`_
      Simplified functions that interface directly with the `PHPCore Request Class`_.

Request Class synopsis
######################

.. code-block:: php

   final class Request {

       /* Static Methods */
       public static function agent(?string $key = null): mixed
       public static function body(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
       public static function cookie(string $key, ?int $filter = null, array|int $options = 0): mixed
       public static function file(string $key): object|null
       public static function files(string $key): array
       public static function format(): string
       public static function host(): string|false
       public static function ipAddress(): string|false
       public static function param(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
       public static function path(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
   }

Request Class Table of Contents
###############################

* :ref:`Request::agent<request-method-agent>` - Get request agent capabilities
* :ref:`Request::body<request-method-body>` - Get data from request body
* :ref:`Request::cookie<request-method-cookie>` - Get data from HTTP cookie
* :ref:`Request::file<request-method-file>` - Get file from request
* :ref:`Request::file<request-method-files>` - Get files from request

Request Class methods
#####################

.. _request-method-agent:
.. php:method:: agent(?string $key = null)

   Get request agent capabilities

   Attempts to determine the capabilities of the user's browser, by looking up the browser's information in the browscap.ini file. Then returns the capability by the given ``$key``.

   If ``$key`` is not passed the entire capabilities object will be returned.

   Returns **NULL** if get_browser() fails or requested capability is unknown.

   :param string $key: The key of the capability data item to retrieve
   :returns: ``mixed`` The request capability or the entire capability object

   .. code-block:: php
      :caption: Get request agent capabilities
      :linenos:
      :emphasize-lines: 7,8,12

      <?php
      use \PHPCore\Request;
      // $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo Request::agent('browser'); // 'Chrome'
      var_dump(Request::agent('istablet')); // false

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Direct chain
      echo Request::agent()->device_type; // 'Desktop'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-body:
.. php:method:: body(?string $key = null, ?int $filter = null, array|int $options = 0)

   Get data from request body

   Will parsed the request body based on the format, then return data from the parsed body by a given $key for data passed via the HTTP POST method. The option ``$filter`` and ``$options`` parameters may be given to invoke filter_var() before the value is returned.

   If ``$key`` is not passed the request body be returned and the ``$filter`` and ``$options`` will be ignored.

   .. seealso::
      `PHP Types of filters`_ - List of available filters and options. 
      `PHP Filter Variable`_ - Information on the operation of the filter_var() function.

   :param string $key: The key of the body's data to retrieve
   :param integer $filter: The ID of the filter to apply
   :param array|int $options: Associative array of options or bitwise disjunction of flags
   :returns: ``mixed`` The requested data item

   .. code-block:: php
      :caption: Get data from request body
      :linenos:
      :emphasize-lines: 7,8,12

      <?php
      use \PHPCore\Request;
      // $_POST = '{ "name": "Smith", "age": "22" }'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo Request::body('name'); // 'Smith'
      var_dump(Request::body('name', FILTER_VALIDATE_INT)); // 22

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Direct chain
      echo Request::body()->age; // '22'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-cookie:
.. php:method:: cookie(string $key, ?int $filter = null, array|int $options = 0)

   Get data from HTTP cookie

   Will return data from cookie by a given $key for data passed via HTTP Cookies. The option ``$filter`` and ``$options`` parameters may be given to invoke filter_var() before the value is returned.

   .. seealso::
      `PHP Types of filters`_ - List of available filters and options. 
      `PHP Filter Variable`_ - Information on the operation of the filter_var() function.

   :param string $key: The key of the cookie to retrieve
   :param integer $filter: The ID of the filter to apply
   :param array|int $options: Associative array of options or bitwise disjunction of flags
   :returns: ``mixed`` The requested data item

   .. code-block:: php
      :caption: Get data from HTTP cookie
      :linenos:
      :emphasize-lines: 7,8

      <?php
      use \PHPCore\Request;
      // $_COOKIE = [ 'OFFSET' => 1, 'ORDER' => 'asc' ]

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo Request::cookie('ORDER'); // 'asc'
      var_dump(Request::cookie('OFFSET', FILTER_VALIDATE_INT)); // 1

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-file:
.. php:method:: file(string $key)

   Get file from request

   Will return the file by a given $key for the files that was uploaded via the HTTP POST method using the $_FILES superglobal variable.

   :param string $key: The key of the file to retrieve
   :returns: ``object|null`` RequestFile object

   .. code-block:: php
      :caption: Get file from request
      :linenos:
      :emphasize-lines: 14,15

      <?php
      use \PHPCore\Request;
      // $_FILES['test'] = [
      //     'name'      => 'sample.pdf.png',
      //     'full_path' => 'sample.pdf.png',
      //     'type'      => 'image/png',
      //     'tmp_name'  => '/tmp/php059gDH',
      //     'error'     => 0,
      //     'size'      => 3028
      // ];

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo Request::file('test')->name; // 'image/png'
      echo Request::file('test')->trueType(); // 'application/pdf'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-files:
.. php:function:: files(string $key)

   Get files from request

   Will return an array of files for a given $key that were uploaded via the HTTP POST method using the $_FILES superglobal variable.

   :param string $key: The key of the array of files to retrieve
   :returns: ``array`` Array of RequestFile objects

   .. code-block:: php
      :caption: Get file from request
      :linenos:
      :emphasize-lines: 14,15

      <?php
      use \PHPCore\Request;
      // $_FILES['test'] = [
      //     'name'      => [ 0 => 'sample.pdf.png' ],
      //     'full_path' => [ 0 => 'sample.pdf.png' ],
      //     'type'      => [ 0 => 'image/png'      ],
      //     'tmp_name'  => [ 0 => '/tmp/php059gDH' ],
      //     'error'     => [ 0 => 0                ],
      //     'size'      => [ 0 => 3028             ]
      // ];

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo Request::files('test')[0]->name; // 'image/png'
      echo Request::files('test')[0]->trueType(); // 'application/pdf'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Functions>`

.. _PHPCore Request Class: ../classes/request.html
.. _PHPCore Request Functions: ../functions/request.html
.. _PHP Filter Variable: https://www.php.net/manual/en/function.filter-var.php
.. _PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
