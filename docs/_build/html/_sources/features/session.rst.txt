===============
Session Feature
===============

PHPCore extends the built-in session handler for PHP with a collection of classes and functions that give some extended capabilities.

.. seealso::
   `PHP Session Functions`_
      Base PHP internal session functions that interface directly with the `PHPCore Session Class`_.
   `PHP SessionHandler Class`_
      Documentation for PHP internal SessionHandler Class.
   `PHPCore Session Functions`_
      The PHPCore session handling functions.
   `PHPCore Session Class`_
      The PHPCore session class.

Metadata
########

Session metadata is used to track additional data for the session. i.g. when it started, who the use is and what ACL groups does the session have access to.

============== ========= ===================================================
      Key      Data-type Description
============== ========= ===================================================
`acl_groups`_    array   ACL groups that have been assigned to this session.
  `started`_    integer  Time session was started or binded to user.
`session_id`_   string   Session ID
`timeleft`_     integer  Seconds until session auto expires.
  `updated`_    integer  Time session was started or binded to user.
   `user`_       string  User identifier.
============== ========= ===================================================


acl_groups
----------
   An array of ACL Groups that have been assigned to this session.

started
-------
   This is when the session was started. Note this time is reset when a user is bound to the session.

session_id
----------
   The unique session id that identifies this session. 

timeleft
--------
   The time left in seconds for this session. Null if not used. See the ``Session.gc_maxlength`` directive for more information.

updated
-------
   The time stamp this session was updated.

user
----
   The user this session has been bound to.

User and ACL Groups
###################

   The ``session_user`` handler allows the session to be bound to a user using a user identifier, i.g. user_id. This will also set a default acl_group via the configuration file directive ``acl_group.default_user``.

   To set the default acl_group when session is not bound to a user via the configuration file directive ``acl_group.default_guest``.

   .. code-block:: ini
      :caption: Set default acl_groups
      :linenos:
      :emphasize-lines: 2,3

      [Session]
      acl_group.default_guest = "GUEST,PUBLIC"
      acl_group.default_user = "USER,PUBLIC"


grant & revoke
--------------

   .. code-block:: php
      :caption: Grant & Revoke
      :linenos:
      :emphasize-lines: 3,4

      <?php
      
      session_grant('SPECIAL');
      session_revoke('GUEST');
      
      ?>

Setting Data with TTL
#####################

   The ``ttl`` option for setting session data allows you to set data that will automatically expire and be removed after a give time in seconds.

   .. code-block:: php
      :caption: Set session data with TTL
      :linenos:
      :emphasize-lines: 4

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


.. _PHPCore Session Feature: ../features/session.html
.. _PHPCore Session Class: ../classes/session.html
.. _PHPCore Session Functions: ../functions/session.html
.. _PHP Session Functions: https://www.php.net/manual/en/ref.session.php
.. _PHP SessionHandler Class: https://www.php.net/manual/en/class.sessionhandler