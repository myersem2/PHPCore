=================
Session Functions
=================

* `session`_ - Get session class instance
* `session_destroy_all`_ - Destroy all sessions
* `session_flash_get`_ - Get session flash data item
* `session_flash_keep`_ - Keep session flash data item
* `session_flash_set`_ - Set session flash data item
* `session_get`_ - Get session data item
* `session_get_metadata`_ - Get session metadata
* `session_grant`_ - Grant session access
* `session_has_access`_ - Check if has access
* `session_revoke`_ - Revoke session access
* `session_set`_ - Set session data item
* `session_user`_ - Get and/or set the current session user

----

Many of the session functions below are just aliases for the methods of the `PHPCore Session Class`_.

.. seealso::
   `PHP Session Functions`_
      Base PHP internal session functions that interface directly with the `PHPCore Session Class`_.
   `PHP SessionHandler Class`_
      Documentation for PHP internal SessionHandler Class.
   `PHPCore Session Feature`_
      The PHPCore extended session handling features.
   `PHPCore Session Class`_
      The PHPCore session class.

-----

.. php:function:: session( )

   Get session class instance

   Returns the current session instance. If the session has not been started yet it will be started before the instance is returned.

   :returns: ``object`` The current Session Class instance

   .. code-block:: php
      :caption: Get session object
      :linenos:
      :emphasize-lines: 6,11

      <?php
      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Set as variable
      $sess = session();
      echo $sess->getMetadata('session_id'); // 'srh6g2amog4hmbkvjpuj8urmfv'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Direct chain
      echo session()->getMetadata('session_id'); // 'srh6g2amog4hmbkvjpuj8urmfv'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_destroy_all( )

   Destroy all sessions

   Destroys **ALL** sessions if the save handlers supports this method.

   :throws Exception: If save handler does not support this method.

   :returns: ``boolean`` Returns true on success or false on failure.

   .. note::
      Only the ``memcached`` save handler is supported at this time.

   .. code-block:: php
      :caption: Destroy all sessions
      :linenos:
      :emphasize-lines: 17

      <?php
      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Set as variable
      $sess = session();

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      echo session_id();                 // string 's319vde2dtjf888clm3m12tdno'
      var_dump($sess->getAllSessions()); // array [
                                         //     '7bh6us2i64eum0vueofbtnsfkl',
                                         //     's319vde2dtjf888clm3m12tdno',
                                         //     'gj636nqn35ifkq9b6dr5ft11iv',
                                         // ]
      session_destroy_all();


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      echo session_id();                 // string 'am59ant4l8meialih84nmd5arv'
      var_dump($sess->getAllSessions()); // array [ 'am59ant4l8meialih84nmd5arv' ]

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_flash_get(?string $key = null)

   Get session flash data item

   This method will return the flash data item that matches the provided key. If a key is not provided the entire flash data array will be returned.

   :param string $key: The key of the flash data item to retrieve
   :returns: ``mixed`` Returns the flash data item

   .. code-block:: php
      :caption: Get flash data
      :linenos:
      :emphasize-lines: 6,8,14,20

      <?php

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump(session_flash_get('my-var')); // null
      session_flash_set('my-var', 123);
      var_dump(session_flash_get('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump(session_flash_get('my-var')); // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump(session_flash_get('my-var')); // null

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_flash_keep(string $key)

   Keep session flash data item

   This method will keep a session flash data item for the next session.

   :param string $key: The key of the flash data item to keep
   :returns: ``boolean`` Return true on success and false if item was not found

   .. code-block:: php
      :caption: Keep flash data
      :linenos:
      :emphasize-lines: 7,9,17,18,31

      <?php

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump(session_flash_get('my-var')); // null
      var_dump(session_flash_keep('my-var'); // false      
      session_flash_set('my-var', 123);
      var_dump(session_flash_keep('my-var'); // false
      var_dump(session_flash_get('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump(session_flash_get('my-var'));          // int 123
      var_dump(session_flash_keep('nonexistent-var'); // false      
      var_dump(session_flash_keep('my-var');          // true      


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump(session_flash_get('my-var'));          // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh

      var_dump(session_flash_get('my-var'));          // null
      var_dump(session_flash_keep('my-var'));         // false

      ?>


   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_flash_set(string $key, mixed $value)

   Set session flash data item

   This method will set a session flash data item to be used for the next session.

   :param string $key: The key of the flash data item
   :param mixed $value: The value of the flash data item

   .. code-block:: php
      :caption: Set flash data
      :linenos:
      :emphasize-lines: 7

      <?php

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // Initial page load

      var_dump(session_flash_get('my-var')); // null
      session_flash_set('my-var', 123);
      var_dump(session_flash_get('my-var')); // null


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh

      var_dump(session_flash_get('my-var')); // int 123


      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh

      var_dump(session_flash_get('my-var')); // null

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_get(string $key = null)

   Get session data item

   This method is used to retrieve a session data item.

   :param string $key: Session Data Key
   :returns: ``mixed`` Session Data

   .. code-block:: php
      :caption: Get session data
      :linenos:
      :emphasize-lines: 5,6,11,12,18,19,25,26

      <?php

      session_set('test_perm', 123);
      session_set('test_temp', time(), 60); // set ttl for 60 sec
      var_dump(session_get('test_perm'));   // int 123
      var_dump(session_get('test_temp'));   // int 1655112668

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh 30 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112698

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh 60 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112728

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh 61 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // null
      var_dump(time());                   // int 1655112729

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_get_metadata(?string $key = null)

   Get session metadata

   This method will return metadata with a provided key. If no key is passed the entire metadata array will be returned.

   :param string $key: Metadata Key
   :returns: ``mixed`` Session Metadata

   .. code-block:: php
      :caption: Get all sessions
      :linenos:
      :emphasize-lines: 3,5

      <?php

      var_dump(session_get_metadata('session_id')); // srh6g2amog4hmbkvjpuj8urmfv
      
      var_dump(session_get_metadata());
      
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

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_grant(string|array $groups)

   Grant session access

   This method grants session access via adding it the the ``acl_groups`` array in the sessions metadata.

   :param string|array $groups: ACL group or array of ACL groups to be granted

   .. code-block:: php
      :caption: Grant session access
      :linenos:
      :emphasize-lines: 4

      <?php

      var_dump(session_has_access('SPECIAL')); // false
      session_grant('SPECIAL');
      var_dump(session_has_access('SPECIAL')); // true
      
      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_has_access(string|array $groups)

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
      :emphasize-lines: 3,5,8-11

      <?php

      var_dump(session_has_access('SPECIAL')); // false
      session_grant('SPECIAL');
      var_dump(session_has_access('SPECIAL')); // true

      session_grant(['RED','BLUE']);
      var_dump(session_has_access(['RED','BLUE']);  // true
      var_dump(session_has_access(['RED','GREEN']); // true
      var_dump(session_has_access(['RED','GREEN'], Session::HAS_ACCESS_ALL); // false
      var_dump(Session_has_access(['RED','GREEN'], Session::HAS_ACCESS_ANY); // true

      ?>


   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_revoke(string|array $groups)

   Revoke session access

   This method removes session access via removing from ``acl_groups`` array in the sessions metadata.

   :param string|array $groups: ACL group or array of ACL groups to be revoked

   .. code-block:: php
      :caption: Revoke session access
      :linenos:
      :emphasize-lines: 5,10

      <?php

      session_grant('SPECIAL');
      var_dump(session_has_access('SPECIAL')); // true
      session_revoke('SPECIAL')
      var_dump(session_has_access('SPECIAL')); // false

      session_grant(['RED','BLUE']);
      var_dump(session_has_access(['RED','BLUE'], Session::HAS_ACCESS_ALL); // true
      session_revoke(['RED','BLUE'])
      var_dump(session_has_access(['RED','BLUE'], Session::HAS_ACCESS_ANY); // false

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_set(string $key, mixed $val, ?int $ttl = null)

   Set session data item

   This method is used to store a session data item. If the optional ``$ttl`` is passed the data item will also be given an expiration.

   :param string $key: Key of session data item to set.
   :param mixed $value: Value of session data item to set.
   :param ?integer $ttl: Time To Live for this data item.

   .. code-block:: php
      :caption: Set session data
      :linenos:
      :emphasize-lines: 3,4

      <?php

      session_set('test_perm', 123);
      session_set('test_temp', time(), 60); // set ttl for 60 sec
      var_dump(session_get('test_perm'));   // int 123
      var_dump(session_get('test_temp'));   // int 1655112668

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 1st page refresh 30 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112698

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 2nd page refresh 60 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // int 1655112668
      var_dump(time());                   // int 1655112728

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // 3rd page refresh 61 sec

      var_dump(session_get('test_perm')); // int 123
      var_dump(session_get('test_temp')); // null
      var_dump(time());                   // int 1655112729

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

-----

.. php:function:: session_user(?string $user = null)

   Get and/or set the current session user

   If the ``$user`` parameter is used the acl_groups will be replaced with the ones declared in the acl_group.default_user directive and the session start time will also be reset.

   :param string $user: The user to bind to the current session
   :return: ``string|null`` User of the current session

   .. code-block:: php
      :caption: Get / Set session user
      :linenos:
      :emphasize-lines: 3-5,9

      <?php

      var_dump(session_user()); // null
      session_user('12345')
      var_dump(session_user()); // string '12345'

      // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // page refresh
      var_dump(session_user()); // string '12345'

      ?>

   .. rst-class:: wy-text-right

      :ref:`Back to list<Session Functions>`

.. _PHPCore Session Feature: ../features/session.html
.. _PHPCore Session Class: ../classes/session.html
.. _PHPCore Session Functions: ../functions/session.html
.. _PHP Session Functions: https://www.php.net/manual/en/ref.session.php
.. _PHP SessionHandler Class: https://www.php.net/manual/en/class.sessionhandler
