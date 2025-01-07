==============
Response Class
==============

The Response class is used to send response via an HTTP request. configuration.

Response Class Synopsis
#######################

.. code-block:: php

   final class Response {

       /* Static Properties */
       private static array $Buffer = [];
       private static ?array $ErrorBuffer;
       private static array $HeaderBuffer = [];
       private static bool $PreventCaching = true;

       /* Static Methods */
       public static function add(array|string $key, mixed $data = null): void
       public static function addHeader(string $header, bool $replace = true): void
       public static function error(int|float $code, array $params = []): void
       public static function formatData(mixed $data): string
       public static function removeHeader(string $header, bool $by_name = false): void
       public static function send(mixed $data = null, ?int $status_code = null): void
       public static function sendHeaders(): void

   }

Response Class Table of Contents
################################

* :ref:`Response::add<response-method-add>` - Add data to response buffer
* :ref:`Response::addHeader<response-method-addheader>` - Add Header
* :ref:`Response::error<response-method-error>` - Send error response
* :ref:`Response::formatData<response-method-formatdata>` - Format data
* :ref:`Response::removeHeader<response-method-removeheader>` - Remove Header
* :ref:`Response::send<response-method-send>` - Send Response
* :ref:`Response::sendHeaders<response-method-sendheaders>` - Send Response Headers

Response Class Methods
######################

.. _response-method-add:
.. php:method:: add(array|string $key, mixed $data = null)
   :noindex:

   Add data to response buffer

   This method is used to add data to the buffer.

   :param array|string $key: Key
   :param mixed $data: Data to be added
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-addheader:
.. php:method:: addHeader(string $header, bool $replace = true)
   :noindex:

   Add Header

   Add a header to the header buffer that will be sent right before the response is sent.

   :param string $header: Header
   :param bool $replace: Replace
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-error:
.. php:method:: error(int|float $code, array $params = [])
   :noindex:

   Send error response

   This method is used to imediatly send an error. The data buffer will not be sent.

   :param int|float $code: The error code to send
   :param array $params: Array of parameters to swap out with {PARAM}
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-formatdata:
.. php:method:: formatData(mixed $data)
   :noindex:

   Format data

   This metho will return the data in the requested format.

   :param mixed $data: Data to format
   :returns: ``string`` Formatted data as a string

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-removeheader:
.. php:method:: removeHeader(string $header, bool $by_name = false)
   :noindex:

   Remove Header

   Remove a header from the header buffer that will be sent when the response sent.

   :param string $header: Header
   :param bool $by_name: By Name
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-send:
.. php:method:: send(mixed $data = null, ?int $status_code = null)
   :noindex:

   Send Response

   Send the headers and buffer to the output buffer.

   :param mixed $data: Data
   :param ?int $status_code: Status Code
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-sendheaders:
.. php:method:: sendHeaders()
   :noindex:

   Send Response Headers

   Send the raw HTTP headers

   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`
