=============
Session Class
=============

The Session class is a special class that has been used to extend the built-in PHP SessionHandler for handling sessions. There are seven methods which wrap the seven internal session save handler callbacks (open, close, read, write, destroy, gc and create_sid). By default, this class will wrap whatever internal save handler is set as defined by the session.save_handler configuration directive which is usually files by default. Other internal session save handlers are provided by PHP extensions such as SQLite (as sqlite), Memcache (as memcache), and Memcached (as memcached).

.. seealso::
   - `PHP Session Functions`_ - Base PHP internal session functions that interface directly with this class.
   - `PHP SessionHandler Class`_ - Documentation for PHP internal SessionHandler Class.
   - `PHPCore Session Feature`_ - The PHPCore extended session handling features.
   - `PHPCore Session Functions`_ - The PHPCore session handling functions.

Session Class Synopsis
######################

.. code-block:: php

   final class Session implements SessionHandlerInterface, SessionIdInterface {

       /* Constants */
       public const PHPINI_OVERRIDES = ["session.cookie_lifetime","session.cookie_path","session.cookie_domain","session.cookie_httponly","session.cookie_samesite","session.gc_probability","session.gc_divisor","session.gc_maxlifetim","session.name","session.save_handler","session.save_path"]

       /* Properties */
       protected array $Config = [];
       protected array $ExpireData = [];
       protected array $FlashData = [];
       protected ?object $Handler = null;
       protected ?array $Metadata = null;

       /* Static Properties */
       protected static ?object $Instance = null;

       /* Methods */
       public function __construct()
       public function close(): bool
       public function create_sid(): string
       public function destroy(string $id): bool
       public function destroyAll(): bool
       public function flashGet(?string $key = null): mixed
       public function flashKeep(string $key): bool
       public function flashSet(string $key, mixed $value): void
       public function gc(int $max_lifetime): int|false
       public function get(?string $key = null): mixed
       public function getAllSessions(): array
       public function getMetadata(?string $key = null): mixed
       public function open(string $path, string $name): bool
       public function read(string $id): string|false
       public function set(string $key, mixed $value, ?int $ttl = null): void
       public function setMetadata(string $key, mixed $value): void
       public function write(string $id, string $data): bool
       private function decrypt(string $data, string $key_phase): string
       private function defaultMetadata(): array
       private function encrypt(string $data, string $key_phase): string
       private function getHandler(): ?object
       private function methodNotSupported(string $method): void

       /* Static Methods */
       public static function getInstance(): object

   }

Session Class Table of Contents
###############################

* :ref:`Session::getInstance<session-method-getinstance>` - Get Instance
* :ref:`Session::__construct<session-method-__construct>` - Constructor
* :ref:`Session::close<session-method-close>` - Close the session
* :ref:`Session::create_sid<session-method-create_sid>` - Return a new session ID
* :ref:`Session::destroy<session-method-destroy>` - Destroy a session
* :ref:`Session::destroyAll<session-method-destroyall>` - Destroy all sessions
* :ref:`Session::flashGet<session-method-flashget>` - Get session flash data item
* :ref:`Session::flashKeep<session-method-flashkeep>` - Keep session flash data item
* :ref:`Session::flashSet<session-method-flashset>` - Set session flash data item
* :ref:`Session::gc<session-method-gc>` - Cleanup old sessions
* :ref:`Session::get<session-method-get>` - Get session data item
* :ref:`Session::getAllSessions<session-method-getallsessions>` - Get all sessions
* :ref:`Session::getMetadata<session-method-getmetadata>` - Get session metadata
* :ref:`Session::open<session-method-open>` - Initialize session
* :ref:`Session::read<session-method-read>` - Read session data
* :ref:`Session::set<session-method-set>` - Set session data item
* :ref:`Session::setMetadata<session-method-setmetadata>` - Set the session metadata
* :ref:`Session::write<session-method-write>` - Write session data
* :ref:`Session::decrypt<session-method-decrypt>` - Decrypt
* :ref:`Session::defaultMetadata<session-method-defaultmetadata>` - Default Meta data
* :ref:`Session::encrypt<session-method-encrypt>` - Encrypt
* :ref:`Session::getHandler<session-method-gethandler>` - Get Handler Helper
* :ref:`Session::methodNotSupported<session-method-methodnotsupported>` - Method Not Supported Helper

Session Class Methods
#####################

.. _session-method-getinstance:
.. php:method:: getInstance()
   :noindex:

   Get Instance

   Returns the class instance for the session class. This method will also invoke the ``__construct()`` method if the instance has not been constructed.

   :returns: ``object`` Session instance

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-__construct:
.. php:method:: __construct()
   :noindex:

   Constructor

   This constructor connects this class instance to the PHP built-in session handler via the **session_set_save_handler()** and starts the session. If the session instance already exists an **Exception** will be thrown.

   :returns: ``void`` 
   :throws: ``Exception`` If instance is already constructed or error

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-close:
.. php:method:: close()
   :noindex:

   Close the session

   Closes the current session. Called internally by PHP SessionHandler.

   :returns: ``bool`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-create_sid:
.. php:method:: create_sid()
   :noindex:

   Return a new session ID

   Generates and returns a new session ID. Called internally by PHP SessionHandler.

   :returns: ``string`` A session ID valid for the default session handler.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-destroy:
.. php:method:: destroy(string $id)
   :noindex:

   Destroy a session

   Destroys a session. Called internally by PHP SessionHandler. In addition to the built-in function PHP provides this method will also clear the ``FlashData`` and reset the session ``Metadata``. This method should normally be invoked by calling the **session_destroy()** function.

   :param string $id: The session ID being destroyed
   :returns: ``bool`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-destroyall:
.. php:method:: destroyAll()
   :noindex:

   Destroy all sessions

   Destroys **ALL** sessions if the save handlers supports this method.

   :returns: ``bool`` Returns true on success or false on failure.
   :throws: ``Exception`` If save handler does not support this method.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-flashget:
.. php:method:: flashGet(?string $key = null)
   :noindex:

   Get session flash data item

   This method will return the flash data item that matches the provided key. If a key is not provided the entire flash data array will be returned.

   :param ?string $key: The key of the flash data item to retrieve
   :returns: ``mixed`` Returns the flash data item

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-flashkeep:
.. php:method:: flashKeep(string $key)
   :noindex:

   Keep session flash data item

   This method will keep a session flash data item for the next session.

   :param string $key: The key of the flash data item to save
   :returns: ``bool`` Return true on success and false if not found

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-flashset:
.. php:method:: flashSet(string $key, mixed $value)
   :noindex:

   Set session flash data item

   This method will set a session flash data item to be used for the next session.

   :param string $key: The key of the flash data item
   :param mixed $value: The value of the flash data item
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-gc:
.. php:method:: gc(int $max_lifetime)
   :noindex:

   Cleanup old sessions

   Cleans up expired sessions. Called internally by PHP SessionHandler.

   :param int $max_lifetime: Sessions that have not updated for the last max_lifetime seconds will be removed.
   :returns: ``int|false`` Returns the number of deleted sessions on success, or false on failure.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-get:
.. php:method:: get(?string $key = null)
   :noindex:

   Get session data item

   This method is used to retrieve a session data item.

   :param ?string $key: Key of session data item to retrieve
   :returns: ``mixed`` Data item from session data

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-getallsessions:
.. php:method:: getAllSessions()
   :noindex:

   Get all sessions

   This method will get all sessions from the handler. Not all save handlers support this method. If it is not supported then an empty array will be returned.

   :returns: ``array`` Sessions from handler
   :throws: ``Exception`` If save handler does not support this method.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-getmetadata:
.. php:method:: getMetadata(?string $key = null)
   :noindex:

   Get session metadata

   This method will return metadata with a provided key. If no key is passed the entire metadata array will be returned.

   :param ?string $key: Metadata Key
   :returns: ``mixed`` Session Metadata

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-open:
.. php:method:: open(string $path, string $name)
   :noindex:

   Initialize session

   Create new session, or re-initialize existing session. Called internally by PHP when a session starts either automatically or when session_start() is invoked.

   :param string $path: The path where to store/retrieve the session.
   :param string $name: The session name.
   :returns: ``bool`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-read:
.. php:method:: read(string $id)
   :noindex:

   Read session data

   Reads the session data from the session storage, and returns the result back to PHP for internal processing. This method is called automatically by PHP when a session is started (either automatically or explicitly with session_start() and is preceded by an internal call to the Session::open().

   :param string $id: The session id to read data for.
   :returns: ``string|false`` Returns an encoded string of the read data. If nothing was read, it must return false. Note this value is returned internally to PHP for processing.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-set:
.. php:method:: set(string $key, mixed $value, ?int $ttl = null)
   :noindex:

   Set session data item

   This method is used to store a session data item. If the optional ``$ttl`` is passed the data item will also be given an expiration.

   :param string $key: Key of session data item to set.
   :param mixed $value: Value of session data item to set.
   :param ?int $ttl: Time To Live for this data item.
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-setmetadata:
.. php:method:: setMetadata(string $key, mixed $value)
   :noindex:

   Set the session metadata

   This method is used to store a session metadata data item.

   :param string $key: Key of session data item to set.
   :param mixed $value: Value of session data item to set.
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-write:
.. php:method:: write(string $id, string $data)
   :noindex:

   Write session data

   Writes the session data to the session storage. Called by normal PHP shutdown, by session_write_close(), or when session_register_shutdown() fails. PHP will call SessionHandler::close() immediately after this method returns.

   :param string $id: The session id
   :param string $data: The encoded session data. This data is the result of the PHP internally encoding the $_SESSION super global to a serialized string and passing it as this parameter. Please note sessions use an alternative serialization method.
   :returns: ``bool`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-decrypt:
.. php:method:: decrypt(string $data, string $key_phase)
   :noindex:

   Decrypt

   Some description...

   :param string $data: Encryped serialize data
   :param string $key_phase: Key phase for decyption
   :returns: ``string`` Descrypted serialize data

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-defaultmetadata:
.. php:method:: defaultMetadata()
   :noindex:

   Default Meta data

   Some description...

   :returns: ``array`` Array of default metadata

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-encrypt:
.. php:method:: encrypt(string $data, string $key_phase)
   :noindex:

   Encrypt

   Some description...

   :param string $data: Descrypted serialize data
   :param string $key_phase: Key phase for encryption
   :returns: ``string`` Encryped serialize data

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-gethandler:
.. php:method:: getHandler()
   :noindex:

   Get Handler Helper

   Some description...

   :returns: ``?object`` Handler Object or NULL if none

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

-----

.. _session-method-methodnotsupported:
.. php:method:: methodNotSupported(string $method)
   :noindex:

   Method Not Supported Helper

   Some description...

   :param string $method: Method Name
   :returns: ``void`` 

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _`PHPCore Session Feature`: ../features/session.html
.. _`PHPCore Session Class`: ../classes/session.html
.. _`PHPCore Session Functions`: ../functions/session.html
.. _`PHP Session Functions`: https://www.php.net/manual/en/ref.session.php
.. _`PHP SessionHandler Class`: https://www.php.net/manual/en/class.sessionhandler
