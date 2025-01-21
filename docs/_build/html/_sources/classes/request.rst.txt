=============
Request Class
=============

The Request class is used to simplify working with data send via the http protocal.

.. seealso::
   `PHPCore Request Functions`_ - Simplified functions that interface directly with the `PHPCore Request Class`_.

Request Class Synopsis
######################

.. code-block:: php

   final class Request {

       /* Static Methods */
       public static function getAgent(?string $key = null): mixed
       public static function getBody(mixed $key = null, int $filter = 516, array|int $options = 0): mixed
       public static function getCookie(?string $key = null, int $filter = 516, array|int $options = 0): mixed
       public static function getFile(string $key, int $flags = 0): ?object
       public static function getFiles(string $key, int $flags = 0): ?array
       public static function getFormat(): ?string
       public static function getHttpHeader(?string $key = null, int $filter = 516, array|int $options = 0): mixed
       public static function getIpAddress(): ?string
       public static function getParameter(?string $key = null, int $filter = 516, array|int $options = 0): mixed
       public static function getSegment(?int $pos = null, int $filter = 516, array|int $options = 0): mixed

   }

Request Class Table of Contents
###############################

* :ref:`Request::getAgent<request-method-getagent>` - Get request agent capabilities
* :ref:`Request::getBody<request-method-getbody>` - Get data from request body
* :ref:`Request::getCookie<request-method-getcookie>` - Get data from HTTP cookie
* :ref:`Request::getFile<request-method-getfile>` - Get file from request
* :ref:`Request::getFiles<request-method-getfiles>` - Get files from request
* :ref:`Request::getFormat<request-method-getformat>` - Get format from request
* :ref:`Request::getHttpHeader<request-method-gethttpheader>` - Get HTTP data from request header
* :ref:`Request::getIpAddress<request-method-getipaddress>` - Get IP address
* :ref:`Request::getParameter<request-method-getparameter>` - Get parameter from requested URI
* :ref:`Request::getSegment<request-method-getsegment>` - Get segment from requested URI

Request Class Methods
#####################

.. _request-method-getagent:
.. php:method:: getAgent(?string $key = null)
   :noindex:

   Get request agent capabilities

   Attempts to determine the capabilities of the user's browser by looking up the browser's information in the browscap.ini file. If the optional **$key** is not provided the entire capabilities object will be returned.

   .. note::
      Returns ``null`` if get_browser() fails or requested capability is unknown.

   :param ?string $key: The key of the capability data item to retrieve.
   :returns: ``mixed`` The request capability or the entire capability object.

   .. code-block:: php
      :caption: Get request agent capabilities
      :linenos:
      :emphasize-lines: 9,10

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'.
      ' AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';
      
      // Get by key
      echo Request::agent('platform'); // 'Win10'
      var_dump(Request::agent('ismobiledevice')); // false
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getbody:
.. php:method:: getBody(mixed $key = null, int $filter = 516, array|int $options = 0)
   :noindex:

   Get data from request body

   Will parsed the request body based on the format, then return data from the parsed body by a given **$key** for data passed via the HTTP POST method. The option **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      - If **$key** is not passed the request body be returned and the **$filter** and **$options** will be ignored.
      - The default for **$filter** is **FILTER_DEFAULT**, which is an alias of **FILTER_UNSAFE_RAW**. This will result in no filtering taking place by default.

   :param mixed $key: Key of the body to retrieve.
   :param int $filter: The filter to apply. Can be a validation filter by using one of the **FILTER_VALIDATE_*** constants, a sanitization filter by using one of the **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a custom filter by using **FILTER_CALLBACK**.
   :param array|int $options: Either an associative array of options, or a bitmask of filter flag constants **FILTER_FLAG_***. If the filter accepts options, flags can be provided by using the "flags" field of array.
   :returns: ``mixed`` The filtered value from the body or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get data from request body
      :linenos:
      :emphasize-lines: 7-9,14-15

      <?php
      
      use \PHPCore\Request;
      
      $_POST = [ 'num' => 123, 'text' => 'abc'];
      
      var_dump(Request::getBody('text')); // 'abc'
      var_dump(Request::getBody('num')); // '123'
      var_dump(Request::getBody()); // [ 'text' => 'abc', 'num' => '123' ]
      
      $_SERVER['CONTENT_TYPE'] = 'text/json';
      // php://input <= {"num":456, "text":"John"}
      
      var_dump(Request::getBody('text')); // 'John'
      var_dump(Request::getBody('num', FILTER_VALIDATE_INT)); // 456
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getcookie:
.. php:method:: getCookie(?string $key = null, int $filter = 516, array|int $options = 0)
   :noindex:

   Get data from HTTP cookie

   Will return data from the HTTP cookie for a given **$key** using the ``$_HEADER`` superglobal varable. The optional **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      - If **$key** is not passed the cookie array be returned and the **$filter** and **$options** will be ignored.
      - The default for **$filter** is **FILTER_DEFAULT**, which is an alias of **FILTER_UNSAFE_RAW**. This will result in no filtering taking place by default.

   :param ?string $key: Key of the cookie to retrieve.
   :param int $filter: The filter to apply. Can be a validation filter by using one of the **FILTER_VALIDATE_*** constants, a sanitization filter by using one of the **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a custom filter by using **FILTER_CALLBACK**.
   :param array|int $options: Either an associative array of options, or a bitmask of filter flag constants **FILTER_FLAG_***. If the filter accepts options, flags can be provided by using the "flags" field of array.
   :returns: ``mixed`` The filtered value from the cookie or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get data from HTTP cookie
      :linenos:
      :emphasize-lines: 7,8-9

      <?php
      
      use \PHPCore\Request;
      
      $_COOKIE = [ 'PaginationOffset' => 1, 'PaginationOrder' => 'asc' ]
      
      echo Request::getCookie('PaginationOrder'); // 'asc'
      var_dump(Request::getCookie('PaginationOffset', FILTER_VALIDATE_INT)); // 1
      var_dump(Request::getCookie('PaginationOrder', FILTER_VALIDATE_INT)); // 1
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getfile:
.. php:method:: getFile(string $key, int $flags = 0)
   :noindex:

   Get file from request

   Will return the file by a given **$key** from the files that were uploaded via the HTTP POST method using the ``$_FILES`` superglobal variable.

   :param string $key: The key of the file to retrieve.
   :param int $flags: Bitwise flags for this method
   :returns: ``?object`` RequestFile object or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get file from request
      :linenos:
      :emphasize-lines: 18-22,24-28

      <?php
      
      use \PHPCore\Request;
      use \PHPCore\RequestFile;
      use \PHPCore\Exceptions\RequestException;
      
      $_FILE = [
      'file_upload' => [
      'name' => 'test.csv',
      'full_path' => 'test.json',
      'type' => 'text/csv',
      'tmp_name' => '/data/test/test.csv',
      'error' => 0,
      'size' => 27
      ]
      ];
      
      $file = Request::getFile('file_upload');
      var_dump($file->getContents()); // '{"name":"Test","value":123}'
      var_dump($file->isTrueType()); // false
      var_dump($file->error); // 9
      var_dump($file->getErrorMessage()); // 'File was not uploaded via HTTP POST'
      
      try {
      $file = Request::getFile('file_upload', RequestFile::EXCEPTION_ON_ERROR);
      } catch (RequestException $e) {
      var_dump($file->getErrorMessage()); // 9
      }
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getfiles:
.. php:method:: getFiles(string $key, int $flags = 0)
   :noindex:

   Get files from request

   Will return an array of files for a given **$key** that were uploaded via the HTTP POST method using the ``$_FILES`` superglobal variable.

   :param string $key: The key of the array of files to retrieve.
   :param int $flags: Bitwise flags for this method
   :returns: ``?array`` Array of RequestFile objects

   .. code-block:: php
      :caption: Get files from request
      :linenos:
      :emphasize-lines: 34-37

      <?php
      
      use \PHPCore\Request;
      
      $_FILE = [
      'file_upload' => [
      'name' => [
      0 => 'test.csv',
      1 => 'test.json'
      ],
      'full_path' => [
      0 => 'test.csv',
      1 => 'test.json'
      ],
      'type' => [
      0 => 'text/csv',
      1 => 'text/csv'
      ]
      'tmp_name' => [
      0 => '/data/test/test.csv',
      1 => '/tmp/phpAKmVxj'
      ],
      'error' => [
      0 => 0,
      0 => 0
      ],
      'size' => [
      0 => 41,
      0 => 27
      ]
      ]
      ];
      
      $files = Request::getFiles('file_upload');
      var_dump($files[1]->getContents()); // '{"name":"Test","value":123}'
      var_dump($files[0]->error); // 9
      var_dump($files[1]->error); // 0
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getformat:
.. php:method:: getFormat()
   :noindex:

   Get format from request

   Will return the format from an HTTP request by first looking at the the ``$_HEADER`` superglobal varable for first the ``CONTENT_TYPE`` and then the ``REQUEST_URI`` to determine the requested format. If format cannot be determine then the ``request.default_format`` declared in the phpcore.ini will be used.

   :returns: ``?string`` The format that was requested.

   .. code-block:: php
      :caption: Get format from request
      :linenos:
      :emphasize-lines: 12,15,18

      <?php
      
      use \PHPCore\Request;
      use \PHPCore\Config;
      
      Config.set('request.default_format', 'text');
      Config.set('request.supported_formats', [ 'text', 'xml', 'json' ]);
      
      $_SERVER['REQUEST_URI'] = '/';
      $_SERVER['CONTENT_TYPE'] = null;
      
      echo Request::getFormat(); // 'csv'
      
      $_SERVER['REQUEST_URI'] = '/resource.xml?query=test';
      echo Request::getFormat(); // 'xml'
      
      $_SERVER['CONTENT_TYPE'] = '/application/json';
      echo Request::getFormat(); // 'json'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-gethttpheader:
.. php:method:: getHttpHeader(?string $key = null, int $filter = 516, array|int $options = 0)
   :noindex:

   Get HTTP data from request header

   Will return data from the HTTP request headers for a given **$key** using the ``$_HEADER`` superglobal varable. The optional **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   The **$key** will be searched for both without then with the prefix "X-" to be compatiable with older conventions. Therfore there is no need include the prefix "X-" in your code moving forward. If both are present the one without the "X-" will be returned.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      - Do not include the "HTTP" prefix to the **$key**.
      - The default for **$filter** is **FILTER_DEFAULT**, which is an alias of **FILTER_UNSAFE_RAW**. This will result in no filtering taking place by default.

   :param ?string $key: Key of the header to retrieve.
   :param int $filter: The filter to apply. Can be a validation filter by using one of the **FILTER_VALIDATE_*** constants, a sanitization filter by using one of the **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a custom filter by using **FILTER_CALLBACK**.
   :param array|int $options: Either an associative array of options, or a bitmask of filter flag constants **FILTER_FLAG_***. If the filter accepts options, flags can be provided by using the "flags" field of array.
   :returns: ``mixed`` The filtered value from the header or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get data from request header
      :linenos:
      :emphasize-lines: 9-11

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
      $_SERVER['HTTP_CUSTOM_HEADER'] = '1';
      $_SERVER['HTTP_X_CUSTOM_HEADER'] = '2';
      
      echo Request::getHeader('accept-encoding'); // 'gzip, deflate'
      echo Request::getHeader('custom-header'); // '1'
      var_dump(Request::getHeader('x-custom-header', FILTER_VALIDATE_INT)); // 1
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getipaddress:
.. php:method:: getIpAddress()
   :noindex:

   Get IP address

   Returns the requester's ip address by the designated ``$_SERVER`` param that contains the requester's IP Address. This is normally ``REMOTE_ADDR`` or ``HTTP_X_FORWARDED_FOR`` and can be configured in the phpcore.ini file via the ``request.ip_server_params`` option.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      Will be **false** if ``$_SERVER`` param is not set or the value does not pass the ``FILTER_VALIDATE_IP`` check.

   :returns: ``?string`` IP address makeing request.

   .. code-block:: php
      :caption: Get data from HTTP cookie
      :linenos:
      :emphasize-lines: 7-9

      <?php
      
      use \PHPCore\Request;
      
      $_COOKIE = [ 'PaginationOffset' => 1, 'PaginationOrder' => 'asc' ]
      
      echo Request::getCookie('PaginationOrder'); // 'asc'
      var_dump(Request::getCookie('PaginationOffset', FILTER_VALIDATE_INT)); // 1
      var_dump(Request::getCookie('PaginationOrder', FILTER_VALIDATE_INT)); // 1
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getparameter:
.. php:method:: getParameter(?string $key = null, int $filter = 516, array|int $options = 0)
   :noindex:

   Get parameter from requested URI

   This method will return the variable passed to the current script via the URL parameters (aka. query string) by a given **$key** using ``$_GET`` superglobal varable. If the optional **$key** is not provided then an array of all the URL parameters will be returned.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      - If **$key** is not provided the **$filter** and **$options** arguments will be ignored.
      - The default for **$filter** is **FILTER_DEFAULT**, which is an alias of **FILTER_UNSAFE_RAW**. This will result in no filtering taking place by default.

   :param ?string $key: Key of the query parameter to retrieve.
   :param int $filter: The filter to apply. Can be a validation filter by using one of the **FILTER_VALIDATE_*** constants, a sanitization filter by using one of the **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a custom filter by using **FILTER_CALLBACK**.
   :param array|int $options: Either an associative array of options, or a bitmask of filter flag constants **FILTER_FLAG_***. If the filter accepts options, flags can be provided by using the "flags" field of array.
   :returns: ``mixed`` The filtered value from the query parameter or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get parameter from requested URI
      :linenos:
      :emphasize-lines: 7-9

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['REQUEST_URI'] = '/index.php?text=abc&num=12345';
      
      var_dump(Request::getParameter()); // [ 'text' => 'abc', 'num' => '12345' ]
      var_dump(Request::getParameter('text')); // 'abc'
      var_dump(Request::getParameter('num', FILTER_VALIDATE_INT)); // 12345
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getsegment:
.. php:method:: getSegment(?int $pos = null, int $filter = 516, array|int $options = 0)
   :noindex:

   Get segment from requested URI

   This method will return a segment of the requested URI with a given **$pos** using the **REQUEST_URI** from the ``$_GET`` superglobal varable.

   .. seealso::
      - `PHP list of validate filters`_ - PHP list of validate filters.
      - `PHP list of sanitize filters`_ - PHP list of sanitize filters.
      - `PHP filter variable`_ - Information on the operation of the PHP ``filter_var()`` function.

   .. note::
      - If **$pos** is not passed the entire segment array will be returned and the **$filter** and **$options** will be ignored.
      - The default for **$filter** is **FILTER_DEFAULT**, which is an alias of **FILTER_UNSAFE_RAW**. This will result in no filtering taking place by default.

   :param ?int $pos: The pos index of the path to retrieve.
   :param int $filter: The filter to apply. Can be a validation filter by using one of the **FILTER_VALIDATE_*** constants, a sanitization filter by using one of the **FILTER_SANITIZE_*** or **FILTER_UNSAFE_RAW**, or a custom filter by using **FILTER_CALLBACK**.
   :param array|int $options: Either an associative array of options, or a bitmask of filter flag constants **FILTER_FLAG_***. If the filter accepts options, flags can be provided by using the "flags" field of array.
   :returns: ``mixed`` The filtered value from the requested segment item or ``null`` if the **$key** does not exist.

   .. code-block:: php
      :caption: Get segment from requested URI
      :linenos:
      :emphasize-lines: 7-11,14

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['REQUEST_URI'] = '/sections/articles/12345.html';
      
      var_dump(Request::getSegment()); // [ 'sections', 'articles', '12345' ]
      var_dump(Request::getSegment(1)); // 'articles'
      var_dump(Request::getSegment(4)); // null
      var_dump(Request::getSegment(2, FILTER_VALIDATE_INT)); // 12345
      var_dump(Request::getSegment(1, FILTER_VALIDATE_INT)); // false
      
      Config.set('request.segment_offset', 1);
      var_dump(Request::segment(0)); // 'articles'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

.. _PHPCore Request Class: ../classes/request.html
.. _PHPCore Request Functions: ../functions/request.html
.. _PHP filter variable: https://www.php.net/manual/en/function.filter-var.php
.. _PHP list of validate filters: https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
.. _PHP list of sanitize filters: https://www.php.net/manual/en/filter.constants.php#constant.filter-sanitize-string
