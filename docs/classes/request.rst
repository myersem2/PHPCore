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

       /* Properties */
       public ?string $RequestId;
       public float $RequestTimeStart;

       /* Methods */
       public function __construct(array $params = [])
       public function getBody(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
       public function getFile(string $key): ?object
       public function getFiles(string $key): array

       /* Static Methods */
       public static function getAgent(?string $key = null): mixed
       public static function getCookie(string $key, ?int $filter = null, array|int $options = 0): mixed
       public static function getFormat(): ?string
       public static function getHeader(string $key, ?int $filter = null, array|int $options = 0): mixed
       public static function getIpAddress(): ?string
       public static function getParameter(?string $key = null, ?int $filter = null, array|int $options = 0): mixed
       public static function getSegment(?int $pos = null, ?int $filter = null, array|int $options = 0): mixed
       private static function filterValue(mixed $value, ?int $filter = null, array|int $options = 0): mixed
       public static function zGetRequest(?string $request_id = null): ?PHPCore\Request

   }

Request Class Table of Contents
###############################

* :ref:`Request::getAgent<request-method-getagent>` - Get request agent capabilities
* :ref:`Request::getCookie<request-method-getcookie>` - Get data from HTTP cookie
* :ref:`Request::getFormat<request-method-getformat>` - Get format from request
* :ref:`Request::getHeader<request-method-getheader>` - Get data from request header
* :ref:`Request::getIpAddress<request-method-getipaddress>` - Get IP address
* :ref:`Request::getParameter<request-method-getparameter>` - Get parameter from requested URI
* :ref:`Request::getSegment<request-method-getsegment>` - Get segment from requested URI
* :ref:`Request::filterValue<request-method-filtervalue>` - Filter value
* :ref:`Request::zGetRequest<request-method-zgetrequest>` - Get request object
* :ref:`Request::__construct<request-method-__construct>` - Constructor
* :ref:`Request::getBody<request-method-getbody>` - Get data from request body
* :ref:`Request::getFile<request-method-getfile>` - Get file from request
* :ref:`Request::getFiles<request-method-getfiles>` - Get files from request

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

.. _request-method-getcookie:
.. php:method:: getCookie(string $key, ?int $filter = null, array|int $options = 0)
   :noindex:

   Get data from HTTP cookie

   Will return data from the HTTP cookie for a given **$key** using the ``$_HEADER`` superglobal varable. The optional **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   :param string $key: The key of the cookie to retrieve.
   :param ?int $filter: The ID of the filter to apply.
   :param array|int $options: Associative array of options or bitwise disjunction of flags.
   :returns: ``mixed`` The requested cookie or ``null`` if it does not exist.

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

.. _request-method-getheader:
.. php:method:: getHeader(string $key, ?int $filter = null, array|int $options = 0)
   :noindex:

   Get data from request header

   Will return data from the HTTP request headers for a given **$key** using the ``$_HEADER`` superglobal varable. The optional **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   The **$key** will be searched for both without then with the prefix "X-" to be compatiable with older conventions. Therfore there is no need include the prefix "X-" in your code moving forward. If both are present the one without the "X-" will be returned.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   .. note::
      Do not include the "HTTP_" prefix to the **$key**.

   :param string $key: The key of the header to retrieve.
   :param ?int $filter: The ID of the filter to apply.
   :param array|int $options: Associative array of options or bitwise disjunction of flags.
   :returns: ``mixed`` The requested header or ``null`` if it does not exist.

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
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   .. note::
      Will be **false** if ``$_SERVER`` param is not set or the value does not pass the ``FILTER_VALIDATE_IP`` check.

   :returns: ``?string`` IP address makeing request.

   .. code-block:: php
      :caption: Get data from HTTP cookie
      :linenos:
      :emphasize-lines: 7,8

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
.. php:method:: getParameter(?string $key = null, ?int $filter = null, array|int $options = 0)
   :noindex:

   Get parameter from requested URI

   This method will return the variable passed to the current script via the URL parameters (aka. query string) by a given **$key** using ``$_GET`` superglobal varable. If the optional **$key** is not provided then an array of all the URL parameters will be returned.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   .. note::
      If **$key** is not provided the **$filter** and **$options** arguments will be ignored.

   :param ?string $key: The key of the query parameter to retrieve.
   :param ?int $filter: The ID of the filter to apply.
   :param array|int $options: Associative array of options or bitwise disjunction of flags.
   :returns: ``mixed`` The requested query item or ``null`` if it does not exist.

   .. code-block:: php
      :caption: Get parameter from requested URI
      :linenos:
      :emphasize-lines: 7-9

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['REQUEST_URI'] = '/index.php?text=abc&num=12345';
      
      var_dump(Request::getParameter()); // [ 'text' => 'abc', 'num' => '12345' ];
      var_dump(Request::getParameter('text')); // 'abc'
      var_dump(Request::getParameter('num', FILTER_VALIDATE_INT)); // 12345
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getsegment:
.. php:method:: getSegment(?int $pos = null, ?int $filter = null, array|int $options = 0)
   :noindex:

   Get segment from requested URI

   This method will return a segment of the requested URI with a given **$pos** using the **REQUEST_URI** from the ``$_GET`` superglobal varable.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   .. note::
      If **$pos** is not passed the entire segment array will be returned and the **$filter** and **$options** will be ignored.

   :param ?int $pos: The pos index of the path to retrieve
   :param ?int $filter: The ID of the filter to apply.
   :param array|int $options: Associative array of options or bitwise disjunction of flags.
   :returns: ``mixed`` The requested segment item or ``null`` if it does not exist.

   .. code-block:: php
      :caption: Get segment from requested URI
      :linenos:
      :emphasize-lines: 7-11,12

      <?php
      
      use \PHPCore\Request;
      
      $_SERVER['REQUEST_URI'] = '/sections/articles/12345.html';
      
      var_dump(Request::getSegment()); // [ "sections", "articles", "12345" ]
      var_dump(Request::getSegment(1)); // 'articles'
      var_dump(Request::getSegment(4)); // null
      var_dump(Request::getSegment(2, FILTER_VALIDATE_INT)); // 12345
      var_dump(Request::getSegment(1, FILTER_VALIDATE_INT)); // false
      
      Config.set('request.segment_offset', 1);
      var_dump(Request::segment(0)); // 'articles'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-filtervalue:
.. php:method:: filterValue(mixed $value, ?int $filter = null, array|int $options = 0)
   :noindex:

   Filter value

   This method will return a filtere value if a filter is specified. If no filter is specified the orginal value will be returned.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   :param mixed $value: Value to be filtered .
   :param ?int $filter: The ID of the filter to apply.
   :param array|int $options: Associative array of options or bitwise disjunction of flags.
   :returns: ``mixed`` The filtered value.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-zgetrequest:
.. php:method:: zGetRequest(?string $request_id = null)
   :noindex:

   Get request object

   This method is used to retrive a previously constructed request instance by a given `$request_id`.

   :param ?string $request_id: Request ID
   :returns: ``?PHPCore\Request`` Request instance

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-__construct:
.. php:method:: __construct(array $params = [])
   :noindex:

   Constructor

   Used to construct the instance and it by reference into the self::$Instances for later use.

   :param array $params: Parameters for request
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getbody:
.. php:method:: getBody(?string $key = null, ?int $filter = null, array|int $options = 0)
   :noindex:

   Get data from request body

   Will parsed the request body based on the format, then return data from the parsed body by a given **$key** for data passed via the HTTP POST method. The option **$filter** and **$options** parameters may be given to invoke ``filter_var()`` before the value is returned.

   If **$key** is not passed the request body be returned and the **$filter** and **$options** will be ignored.

   .. seealso::
      - `PHP Types of filters`_ - List of available filters and options.
      - `PHP Filter Variable`_ - Information on the operation of the ``filter_var()`` function.

   :param ?string $key: The key of the body's data to retrieve
   :param ?int $filter: The ID of the filter to apply
   :param array|int $options: Associative array of options or bitwise disjunction of flags
   :returns: ``mixed`` The requested data item

   .. code-block:: php
      :caption: Get data from request body
      :linenos:
      :emphasize-lines: 8,9

      <?php
      
      use \PHPCore\Request;
      
      // $_POST = '{ "name": "Smith", "age": "22" }'
      
      // Get by key
      echo Request::body('name'); // 'Smith'
      var_dump(Request::body('name', FILTER_VALIDATE_INT)); // 22
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getfile:
.. php:method:: getFile(string $key)
   :noindex:

   Get file from request

   Will return the file by a given **$key** for the files that was uploaded via the HTTP POST method using the ``$_FILES`` superglobal variable.

   :param string $key: The key of the file to retrieve
   :returns: ``?object`` RequestFile object

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
      
      echo Request::file('test')->type; // 'image/png'
      echo Request::file('test')->trueType(); // 'application/pdf'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

-----

.. _request-method-getfiles:
.. php:method:: getFiles(string $key)
   :noindex:

   Get files from request

   Will return an array of files for a given **$key** that were uploaded via the HTTP POST method using the ``$_FILES`` superglobal variable.

   :param string $key: The key of the array of files to retrieve
   :returns: ``array`` Array of RequestFile objects

   .. code-block:: php
      :caption: Get files from request
      :linenos:
      :emphasize-lines: 14,15

      <?php
      
      use \PHPCore\Request;
      
      // $_FILES['test'] = [
      //     'name'      => [ 'sample_1.pdf.png', 'sample_2.csv' ],
      //     'full_path' => [ 'sample_1.pdf.png', 'sample_2.csv' ],
      //     'type'      => [ 'image/png', text/csv', ],
      //     'tmp_name'  => [ '/tmp/php059gDH', '/tmp/phpWGy7GA' ],
      //     'error'     => [ 0, 0 ],
      //     'size'      => [ 3028, 1037 ],
      // ];
      
      echo Request::file('test')[0]->name; // 'sample_1.pdf.png'
      echo Request::file('test')[1]->name; // 'sample_2.csv'
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Class Table of Contents>`

.. _PHPCore Request Class: ../classes/request.html
.. _PHPCore Request Functions: ../functions/request.html
.. _PHP Filter Variable: https://www.php.net/manual/en/function.filter-var.php
.. _PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
