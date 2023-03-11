=================
Request Functions
=================

* `request_agent`_ - Get request agent capabilities
* `request_body`_ - Get data from request body.
* `request_cookie`_ - Get data from HTTP cookie.

----

Many of the request functions below are just aliases for the methods of the `PHPCore Request Class`_.

.. seealso::
   `PHPCore Request Class`_ The PHPCore request class.

.. php:function:: request_agent(?string $key = null)

   Get request agent capabilities

   Attempts to determine the capabilities of the user's browser, by looking up the browser's information in the browscap.ini file. Then returns the capability by the given ``$key``.

   If ``$key`` is not passed the entire capabilities object will be returned.

   Returns **NULL** if get_browser() fails or requested capability is unknown.

   :param string $key: The key of the capability data item to retrieve
   :returns: ``mixed`` The request capability or the entire capability object

   .. code-block:: php
      :caption: Get request agent capabilities
      :linenos:
      :emphasize-lines: 6,7,11

      <?php
      // $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo request_agent('browser'); // 'Chrome'
      var_dump(request_agent('istablet')); // false

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Direct chain
      echo request_agent()->device_type; // 'Desktop'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Functions>`

-----

.. php:function:: request_body(?string $key = null, ?int $filter = null, array|int $options = 0)

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
      :emphasize-lines: 6,7,11

      <?php
      // $_POST = '{ "name": "Smith", "age": "22" }'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo request_body('name'); // 'Smith'
      var_dump(request_body('name', FILTER_VALIDATE_INT)); // 22

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Direct chain
      echo request_body()->age; // '22'

      ?>


   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Functions>`


-----

.. php:function:: request_cookie(string $key, ?int $filter = null, array|int $options = 0)

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
      :emphasize-lines: 6,7

      <?php
      // $_COOKIE = [ 'OFFSET' => 1, 'ORDER' => 'asc' ]

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Get capability by key
      echo request_cookie('ORDER'); // 'asc'
      var_dump(request_cookie('OFFSET', FILTER_VALIDATE_INT)); // 1

      ?>


   .. rst-class:: wy-text-right

      :ref:`Back to list<Request Functions>`

.. _PHPCore Request Class: ../classes/request.html
.. _PHP Filter Variable: https://www.php.net/manual/en/function.filter-var.php
.. _PHP Types of filters: https://www.php.net/manual/en/filter.filters.php
