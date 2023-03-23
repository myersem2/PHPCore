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
       public static function header(string $key, ?int $filter = null, array|int $options = 0): mixed
       public static function host(): string|false
       public static function ip(): string|false
       public static function param(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
       public static function path(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
   }

Request Class Table of Contents
###############################

* :ref:`Request::agent<request-method-agent>` - Get request agent capabilities
* :ref:`Request::body<request-method-body>` - Get data from request body
* :ref:`Request::cookie<request-method-cookie>` - Get data from HTTP cookie
* :ref:`Request::file<request-method-file>` - Get file from request
* :ref:`Request::files<request-method-files>` - Get files from request
* :ref:`Request::header<request-method-header>` - Get data from request header
* :ref:`Request::host<request-method-host>` - Get requester host name
* :ref:`Request::ip<request-method-ip>` - Get requester ip address

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
      // Get by key
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
      // Get by key
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
      :emphasize-lines: 5,6

      <?php
      use \PHPCore\Request;
      // $_COOKIE = [ 'OFFSET' => 1, 'ORDER' => 'asc' ]

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
      :emphasize-lines: 12,13

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
      :caption: Get files from request
      :linenos:
      :emphasize-lines: 12,13

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

      echo Request::files('test')[0]->name; // 'image/png'
      echo Request::files('test')[0]->trueType(); // 'application/pdf'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-header:
.. php:function:: header(string $key, ?int $filter = null, array|int $options = 0)

   Get data from request header

   Will return data from the HTTP request headers for a given $key. The option ``$filter`` and ``$options`` parameters may be given to invoke filter_var() before the value is returned.

   The key will be searched for both without then with the prefix "x-" to be compatiable with older conventions. Therfore there is no need include the prefix "x-" in your code moving forward.

   .. seealso::
      `PHP Types of filters`_ - List of available filters and options. 
      `PHP Filter Variable`_ - Information on the operation of the filter_var() function.

   :param string $key: The key of the header's data to retrieve
   :param integer $filter: The ID of the filter to apply
   :param array|int $options: Associative array of options or bitwise disjunction of flags
   :returns: ``mixed`` The requested header item

   .. code-block:: php
      :caption: Get data from request header
      :linenos:
      :emphasize-lines: 13,14,15,17

      <?php
      use \PHPCore\Request;
      // Request Headers
      //   Accept: */*
      //   Accept-Encoding: gzip, deflate
      //   Accept-Language: en-US,en;q=0.9
      //   Connection: keep-alive
      //   Content-Length: 0
      //   User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36
      //   x-custom-header-1: Random Text
      //   x-custom-header-2: 12345

      echo Request::header('accept-encoding'); // 'gzip, deflate'
      echo Request::header('custom-header-1'); // 'Random Text'
      echo Request::header('x-custom-header-1'); // 'Random Text'

      var_dump(Request::header('custom-header-2', FILTER_VALIDATE_INT)); // 12345

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-host:
.. php:function:: host()

   Get requester host name

   This method will return the requester's host name using the requester's ip address, see Request::ipAddress() for more information.

   Returns false if requester ip address is unknown.

   :returns: ``string|false`` Host name

   .. code-block:: php
      :caption: Get requester host name
      :linenos:
      :emphasize-lines: 5,8

      <?php
      use \PHPCore\Request;

      // $_SERVER['REMOTE_ADDR'] = '8.8.8.8'
      echo Request::host(); // 'dns.google'

      // $_SERVER['REMOTE_ADDR'] = '123456'
      var_dump(Request::host()); // false

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

-----

.. _request-method-ip:
.. php:function:: ip()

   Get requester ip address

   This method will return the requester's ip address via the designated $_SERVER param that contains the requester's IP Address. This is normally REMOTE_ADDR or HTTP_X_FORWARDED_FOR and can be configured in the phpcore.ini file.

   Returns false if $_SERVER param is not set.

   :returns: ``string|false`` IP Address of requester

   .. code-block:: php
      :caption: Get requester ip address
      :linenos:
      :emphasize-lines: 7,10

      <?php
      use \PHPCore\Request;
      // $_SERVER['REMOTE_ADDR'] = '10.0.0.1'
      // $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.0.1'

      // phpcore.ini: request.ip_var = "REMOTE_ADDR"
      echo Request::ip(); // '10.0.0.1'

      // phpcore.ini: request.ip_var = "HTTP_X_FORWARDED_FOR"
      echo Request::ip(); // '192.168.0.1'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table Of Contents>`

.. _PHPCore Request Class: ../classes/request.html
.. _PHPCore Request Functions: ../functions/request.html
.. _PHP Filter Variable: https://www.php.net/manual/en/function.filter-var.php
.. _PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
