=============
Session Class
=============

The Session class is a special class that has been used to extend the built-in PHP SessionHandler for handling sessions. There are seven methods which wrap the seven internal session save handler callbacks (open, close, read, write, destroy, gc and create_sid). By default, this class will wrap whatever internal save handler is set as defined by the session.save_handler configuration directive which is usually files by default. Other internal session save handlers are provided by PHP extensions such as SQLite (as sqlite), Memcache (as memcache), and Memcached (as memcached).

.. seealso::
   `PHP Session Functions`_
      Base PHP internal session functions that interface directly with the `PHPCore Session Class`_.
   `PHP SessionHandler Class`_
      Documentation for PHP internal SessionHandler Class.
   `PHPCore Session Feature`_
      The PHPCore extended session handling features.
   `PHPCore Session Functions`_
      The PHPCore session handling functions.

Session Class synopsis
######################

.. code-block:: php

   class Session extends \SessionHandler {
 
       use CoreTrait;
 
       const HAS_ACCESS_ANY = 1;  // Has Access check true on ANY match 
       const HAS_ACCESS_ALL = 2;  // Has Access check true if ALL match
 
       /* Methods */
       public function __construct()
       public function close(): bool
       public function create_sid(): string
       public function destroy(string $id): bool
       public function destroyAll(): bool
       public function flashGet(string $key): mixed
       public function flashKeep(string $key): bool
       public function flashSet(string $key, mixed $value): void
       public function get(string $key): mixed
       public function getAllSessions(): array
       public function getMetadata(?string $key = null): mixed
       public function gc(int $max_lifetime): int|false
       public function grant(string|array $groups): void
       public function hasAccess(string $group): bool
       public function open(string $path, string $name): bool
       public function read(string $id): string|false
       public function revoke(string|array $groups): void
       public function set(string $key, mixed $val, ?int $ttl = null): void
       public function user(?string $user = null): vstring|null
       public function write(string $id, string $data): bool
   }
 
.. warning::
   There are some directives within the php.ini that **MUST** set for the PHPCore Session feature to work correctly.

   * ``session.auto_start = 0``  *this is the default value*
   * ``session.serialize_handler = "php_serialize"`` *The default is "php"*  

.. note::
   You can use the phpcore.ini Session :ref:`Configuration Directives.auto_start` directive if you want the session to automatically start as
   
Session Class Table of Contents
###############################

* :ref:`Session::__construct<session-method-construct>` - Constructor
* :ref:`Session::close<session-method-close>` - Close the session
* :ref:`Session::create_sid<session-method-create_sid>` - Return a new session ID
* :ref:`Session::destroy<session-method-destroy>` - Destroy a session
* :ref:`Session::destroyAll<session-method-destroy-all>` - Destroy ALL sessions
* :ref:`Session::flashGet<session-method-flash-get>` - Get session flash data item
* :ref:`Session::flashKeep<session-method-flash-keep>` - Keep session flash data item
* :ref:`Session::flashSet<session-method-flash-set>` - Set session flash data item
* :ref:`Session::get<session-method-get>` - Get session data item
* :ref:`Session::getAllSessions<session-method-get-all-sessions>` - Set session flash data item
* :ref:`Session::getMetadata<session-method-get-metadata>` - Get session metadata
* :ref:`Session::gc<session-method-gc>` - Cleanup old sessions
* :ref:`Session::grant<session-method-grant>` - Grant session access
* :ref:`Session::hasAccess<session-method-has-access>` - Check if has access
* :ref:`Session::open<session-method-open>` - Initialize session
* :ref:`Session::read<session-method-read>` - Read session data
* :ref:`Session::revoke<session-method-revoke>` - Revoke session access
* :ref:`Session::set<session-method-set>` - Set session data item
* :ref:`Session::user<session-method-user>` - Get and/or set the current session user
* :ref:`Session::write<session-method-write>` - Write session data

Session Class methods
#####################

.. _session-method-construct:
.. php:method:: __construct( )

   Constructor

   This constructor connects this class instance to the PHP built-in session handler via the **session_set_save_handler()** and starts the session. If the session instance already exists an **Exception** will be thrown.

   :throws Exception: Session instance has already been constructed

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-close:
.. php:method:: close( )

   Close the session

   Closes the current session. Called internally by PHP SessionHandler.

   See `PHP SessionHandler::close`_ for more information the PHP built-in functionality of the this method.

   :returns: ``boolean`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-create_sid:
.. php:method:: create_sid( )

   Return a new session ID

   Generates and returns a new session ID. Called internally by PHP SessionHandler.

   See `PHP SessionHandler::create_sid`_ for more information the PHP built-in functionality of the this method.

   :returns: ``string`` This function has no parameters.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-destroy:
.. php:method:: destroy(string $id)

   Destroy a session

   Destroys a session. Called internally by PHP SessionHandler. In addition to the built-in function PHP provides this method will also clear the ``FlashData`` and resets the session ``SessionMetadata``. This method should normally be invoked by calling the **session_destroy()** function.

   See `PHP SessionHandler::destroy`_ for more information the PHP built-in functionality of the this method.

   :param string $id: The session ID being destroyed
   :returns: ``boolean`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-destroy-all:
.. php:method:: destroyAll( )

   Destroy all sessions

   Destroys **ALL** sessions if the save handlers supports this method.

   :throws Exception: If save handler does not support this method.

   :returns: ``boolean`` Returns true on success or false on failure.

   .. note::
      Only the ``memcached`` save handler is supported at this time.

   .. code-block:: php
      :caption: Destroy all sessions
      :linenos:
      :emphasize-lines: 15

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      echo session_id();                 // string 's319vde2dtjf888clm3m12tdno'
      var_dump($sess->getAllSessions()); // array [
                                         //     '7bh6us2i64eum0vueofbtnsfkl',
                                         //     's319vde2dtjf888clm3m12tdno',
                                         //     'gj636nqn35ifkq9b6dr5ft11iv',
                                         // ]
      $sess->destroyAll();


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      echo session_id();                 // string 'am59ant4l8meialih84nmd5arv'
      var_dump($sess->getAllSessions()); // array [ 'am59ant4l8meialih84nmd5arv' ]

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-flash-get:
.. php:method:: flashGet(?string $key = null)

   Get session flash data item

   This method will return the flash data item that matches the provided key. If a key is not provided the entire flash data array will be returned.

   :param string $key: The key of the flash data item to retrieve
   :returns: ``mixed`` Returns the flash data item

   .. code-block:: php
      :caption: Get flash data
      :linenos:
      :emphasize-lines: 8,10,16,22

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump($sess->flashGet('my-var')); // null
      $sess->flashSet('my-var', 123);
      var_dump($sess->flashGet('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump($sess->flashGet('my-var')); // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump($sess->flashGet('my-var')); // null

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-flash-keep:
.. php:method:: flashKeep(string $key)

   Keep session flash data item

   This method will keep a session flash data item for the next session.

   :param string $key: The key of the flash data item to keep
   :returns: ``boolean`` Return true on success and false if item was not found

   .. code-block:: php
      :caption: Keep flash data
      :linenos:
      :emphasize-lines: 9,11,19,20,33

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump($sess->flashGet('my-var')); // null
      var_dump($sess->flashKeep('my-var'); // false      
      $sess->flashSet('my-var', 123);
      var_dump($sess->flashKeep('my-var'); // false
      var_dump($sess->flashGet('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump($sess->flashGet('my-var'));          // int 123
      var_dump($sess->flashKeep('nonexistent-var'); // false      
      var_dump($sess->flashKeep('my-var');          // true      


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump($sess->flashGet('my-var'));          // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh

      var_dump($sess->flashGet('my-var'));          // null
      var_dump($sess->flashKeep('my-var'));         // false

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-flash-set:
.. php:method:: flashSet(string $key, mixed $value)

   Set session flash data item

   This method will set a session flash data item to be used for the next session.

   :param string $key: The key of the flash data item
   :param mixed $value: The value of the flash data item

   .. code-block:: php
      :caption: Set flash data
      :linenos:
      :emphasize-lines: 9

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump($sess->flashGet('my-var')); // null
      $sess->flashSet('my-var', 123);
      var_dump($sess->flashGet('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump($sess->flashGet('my-var')); // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump($sess->flashGet('my-var')); // null

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-get:
.. php:method:: get(string $key)

   Get session data item

   This method is used to retrieve a session data item.

   :param string $key: Session Data Key
   :returns: ``mixed`` Session Data

   .. code-block:: php
      :caption: Get session data
      :linenos:
      :emphasize-lines: 7,8,13,14,20,21,27,28

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      $sess->set('test_perm', 123);
      $sess->set('test_temp', time(), 60); // set ttl for 60 sec
      var_dump($sess->get('test_perm'));   // int 123
      var_dump($sess->get('test_temp'));   // int 1655112668

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh 30 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // int 1655112668
      var_dump(time());                  // int 1655112698

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh 60 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // int 1655112668
      var_dump(time());                  // int 1655112728

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh 61 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // null
      var_dump(time());                  // int 1655112729

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-get-all-sessions:
.. php:method:: getAllSessions( )

   Get all sessions

   This method will get all sessions from the handler. Not all save handlers support this method. If it is not supported then an empty array will be returned.

   :throws Exception: If save handler does not support this method.

   :returns: ``array`` Sessions from handler

   .. note::
      Only the ``memcached`` save handler is supported at this time.

   .. code-block:: php
      :caption: Get all sessions
      :linenos:
      :emphasize-lines: 6

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      echo session_id();                 // string 's319vde2dtjf888clm3m12tdno'
      var_dump($sess->getAllSessions()); // array [
                                         //     '7bh6us2i64eum0vueofbtnsfkl',
                                         //     's319vde2dtjf888clm3m12tdno',
                                         //     'gj636nqn35ifkq9b6dr5ft11iv',
                                         // ]

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-get-metadata:
.. php:method:: getMetadata( )

   Get session metadata

   This method will return metadata with a provided key. If no key is passed the entire metadata array will be returned.

   :param string $key: Metadata Key
   :returns: ``mixed`` Session Metadata

   .. code-block:: php
      :caption: Get all sessions
      :linenos:
      :emphasize-lines: 5

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      var_dump($sess->getMetadata());
      
      /*
      Above will output something similar to:
      
      [
        'acl_groups' => [ 'GUEST', 'PUBLIC' ],
        'started'    => 1654647866,
        'session_id' => 'srh6g2amog4hmbkvjpuj8urmfv',
        'timeleft'   => 27376,
        'updated'    => 1654648614,
        'user'       => null,
      ]
      
      */

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-gc:
.. php:method:: gc( )

   Cleanup old sessions

   Cleans up expired sessions. Called internally by PHP SessionHandler.

   :param string $key: Metadata Key
   :returns: ``int|false`` Returns the number of deleted sessions on success, or false on failure.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-grant:
.. php:method:: grant(string|array $groups)

   Grant session access

   This method grants session access via adding it the the ``acl_groups`` array in the sessions metadata.

   :param string|array $groups: ACL group or array of ACL groups to be granted

   .. code-block:: php
      :caption: Grant session access
      :linenos:
      :emphasize-lines: 6,9

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      var_dump($sess->hasAccess('SPECIAL')); // false
      $sess->grant('SPECIAL');
      var_dump($sess->hasAccess('SPECIAL')); // true

      $sess->grant(['RED','BLUE']);
      var_dump($sess->hasAccess('RED'));     // true
      var_dump($sess->hasAccess('BLUE'));    // true
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-has-access:
.. php:method:: hasAccess(string|array $groups, int $flags = 0)

   Check if has access

   This method checks if a session has access via checking if in the ``acl_groups`` array in the sessions metadata.

   :param string|array $groups: ACL group or array of ACL groups to check access for
   :param integer $flags: Bitwise flags for this method
   :flag: ``Session::HAS_ACCESS_ANY`` Has Access check true on ANY match 
   :flag: ``Session::HAS_ACCESS_ALL`` Has Access check true if ALL match
   :return: ``boolean`` If has session access

   .. code-block:: php
      :caption: Check for session access
      :linenos:
      :emphasize-lines: 5,7,10-13

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      var_dump($sess->hasAccess('SPECIAL'));      // false
      $sess->grant('SPECIAL');
      var_dump($sess->hasAccess('SPECIAL'));      // true

      $sess->grant(['RED','BLUE']);
      var_dump($sess->hasAccess(['RED','BLUE']);  // true
      var_dump($sess->hasAccess(['RED','GREEN']); // true
      var_dump($sess->hasAccess(['RED','GREEN'], Session::HAS_ACCESS_ALL); // false
      var_dump($sess->hasAccess(['RED','GREEN'], Session::HAS_ACCESS_ANY); // true

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-open:
.. php:method:: open( )

   Initialize session

   Create new session, or re-initialize existing session. Called internally by PHP when a session starts either automatically or when session_start() is invoked.

   See `PHP SessionHandler::open`_ for more information the PHP built-in functionality of the this method.

   :returns: ``boolean`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-read:
.. php:method:: read( )

   Read session data

   Reads the session data from the session storage, and returns the result back to PHP for internal processing. This method is called automatically by PHP when a session is started (either automatically or explicitly with session_start() and is preceded by an internal call to the Session::open().

   See `PHP SessionHandler::read`_ for more information the PHP built-in functionality of the this method.

   :param string $id: The session id to read data for.
   :returns: ``string|false`` Returns an encoded string of the read data. If nothing was read, it must return false. Note this value is returned internally to PHP for processing.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

-----

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-revoke:
.. php:method:: revoke(string|array $groups)

   Revoke session access

   This method removes session access via removing from ``acl_groups`` array in the sessions metadata.

   :param string|array $groups: ACL group or array of ACL groups to be revoked

   .. code-block:: php
      :caption: Check for session access
      :linenos:
      :emphasize-lines: 7,12

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      $sess->grant('SPECIAL');
      var_dump($sess->hasAccess('SPECIAL')); // true
      $sess->revoke('SPECIAL')
      var_dump($sess->hasAccess('SPECIAL')); // false

      $sess->grant(['RED','BLUE']);
      var_dump($sess->hasAccess(['RED','BLUE'], Session::HAS_ACCESS_ALL); // true
      $sess->revoke(['RED','BLUE'])
      var_dump($sess->hasAccess(['RED','BLUE'], Session::HAS_ACCESS_ANY); // false

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-set:
.. php:method:: set(string $key, mixed $value, ?int $ttl = null)

   Set session data item

   This method is used to store a session data item. If the optional ``$ttl`` is passed the data item will also be given an expiration.

   :param string $key: Key of session data item to set.
   :param mixed $value: Value of session data item to set.
   :param integer $ttl: Time To Live for this data item.

   .. code-block:: php
      :caption: Set session data
      :linenos:
      :emphasize-lines: 5,6

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      $sess->set('test_perm', 123);
      $sess->set('test_temp', time(), 60); // set ttl for 60 sec
      var_dump($sess->get('test_perm'));   // int 123
      var_dump($sess->get('test_temp'));   // int 1655112668

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh 30 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112698

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh 60 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112728

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh 61 sec

      var_dump($sess->get('test_perm')); // int 123
      var_dump($sess->get('test_temp')); // null
      var_dump(time());                   // int 1655112729

      ?>


   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-user:
.. php:method:: user(?string $name = null)

   Get and/or set the current session user

   If the ``$user`` parameter is used the acl_groups will be replaced with the ones declared in the acl_group.default_user directive and the session start time will also be reset.

   :param string $user: The user to bind to the current session
   :return: ``string|null`` User of the current session

   .. code-block:: php
      :caption: Get / Set session user
      :linenos:
      :emphasize-lines: 5-7,11

      <?php
      use \PHPCore\Session;
      $sess = Session::getInstance();

      var_dump($sess->user()); // null
      $sess->user('12345')
      var_dump($sess->user()); // string '12345'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // page refresh
      var_dump($sess->user()); // string '12345'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _session-method-write:
.. php:method:: write(string $id, string $data)

   Write session data

   Writes the session data to the session storage. Called by normal PHP shutdown, by session_write_close(), or when session_register_shutdown() fails. PHP will call SessionHandler::close() immediately after this method returns.

   :param string $id: The session id
   :param string $data: The encoded session data. This data is the result of the PHP internally encoding the $_SESSION superglobal to a serialized string and passing it as this parameter. Please note sessions use an alternative serialization method.
   :return: ``boolean`` The return value (usually true on success, false on failure). Note this value is returned internally to PHP for processing.

   .. note::
      This method should not be called directly, it should only be invoked by the session_handler.

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Class Table of Contents>`

.. _PHPCore Session Feature: ../features/session.html
.. _PHPCore Session Class: ../classes/session.html
.. _PHPCore Session Functions: ../functions/session.html
.. _PHP SessionHandler Class: https://www.php.net/manual/en/class.sessionhandler
.. _PHP SessionHandler::close: https://www.php.net/manual/en/sessionhandler.close.php
.. _PHP SessionHandler::create_sid: https://www.php.net/manual/en/sessionhandler.create-sid.php
.. _PHP SessionHandler::destroy: https://www.php.net/manual/en/sessionhandler.destroy.php
.. _PHP SessionHandler::open: https://www.php.net/manual/en/sessionhandler.open.php
.. _PHP SessionHandler::read: https://www.php.net/manual/en/sessionhandler.read.php
.. _PHP Session Functions: https://www.php.net/manual/en/ref.session.php
