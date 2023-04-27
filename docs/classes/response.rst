==============
Response Class
==============



Response Class Synopsis
#######################

.. code-block:: php

   final class Response {

       use PHPCore\Core

       /* Static Properties */
       private static array $Buffer = [];
       private static ?array $ErrorBuffer = [];
       private static array $HeaderBuffer = [];
       private static bool $PreventCaching = true;

       /* Static Methods */
       public static function add(array|string $key, mixed $data = null): void
       public static function addHeader(string $header, bool $replace = true): void

   }

Response Class Table of Contents
################################

* :ref:`Response::add<response-method-add>` - TODO: Document
* :ref:`Response::addHeader<response-method-addheader>` - Add Header

Response Class Methods
######################

.. _response-method-add:
.. php:method:: add(array|string $key, mixed $data = null)

   TODO: Document

   

   :param array|string $key: Key
   :param mixed $data: Data to be added
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`

-----

.. _response-method-addheader:
.. php:method:: addHeader(string $header, bool $replace = true)

   Add Header

   Add a header to the header buffer that will be sent right before the response is sent.

   :param string $header: Header
   :param bool $replace: Replace
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Response Class Table of Contents>`
