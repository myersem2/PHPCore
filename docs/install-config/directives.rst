======================
phpcore.ini directives
======================

Session Directives
##################

The session directives that are outlined on the `PHP Session Runtime Configuration`_ documentation can also be set in the phpcore configuration. Only the methods that differ will be included below.

========================== ========= ===================================================
             Key           Data-type Description
========================== ========= ===================================================
`save_handler_id_prefix`_   string   Save handler prefix
`auto_start`_               boolean  Initialize session on request startup.
`cookie_autodestroy`_       boolean  Will cookie be delete when the session is destroyed.
`gc_maxlength`_             integer  Max session time.
`encrypt`_                  boolean  Encrypt session data before giving to handler.
`key_phase`_                 string  String to use for encrypting session data.
`acl_group`_                 string  CSV of default acl groups.
========================== ========= ===================================================

.. _Configuration Directives.auto_start:

save_handler_id_prefix
----------------------

   Save handler prefix if using cache pool.

auto_start
----------

   Initialize session on request startup.

   ``auto_start = Yes|No`` specifies whether the session module starts a session automatically on request startup. Defaults to 0 (disabled).

   .. warning::
      This is required to be set to ``session.auto_start = 0`` in the ``php.ini`` you may choose to override in the ``phpcore.ini`` failure to do this will result in PHPCore Session mis operation.

   .. code-block:: ini
      :caption: Session auto start
      :linenos:
      :emphasize-lines: 2
      
      [Session]
      auto_start = Yes

   .. _PHP Session Runtime Configuration: https://www.php.net/manual/en/session.configuration.php

cookie_autodestroy
------------------

   ``cookie_autodestroy = Yes|No`` specifies whether or not the cookie will delete when the session is destroyed.

   .. code-block:: ini
      :caption: Session auto destroy
      :linenos:
      :emphasize-lines: 2
      
      [Session]
      cookie_autodestroy = Yes

gc_maxlength
------------

   After this number of seconds the session will be considered too long in length and its data will be cleared out. This is determined by the session start time and the current time when the session is read. Set to None if there is no max length a session can. This is also known as max session time.

   .. code-block:: ini
      :caption: Session max time
      :linenos:
      :emphasize-lines: 3,5
      
      [Session]
      ; None: Sessions can live forever
      gc_maxlength = None
      ; Integer: (28800 sec = 8 hrs) Sessions can live for a max of 8 hours then are auto-destroyed
      ;gc_maxlength = 28800

encrypt
-------

   Automatically encrypts and decrypts data when it is written or read from the the session save_handler.

   .. code-block:: ini
      :caption: Session encrypt
      :linenos:
      :emphasize-lines: 2
      
      [Session]
      encrypt = Yes

key_phase
---------

   Defines the key phase to be used to salt the hash for the save_handler encryption process.

   .. code-block:: ini
      :caption: Session key phase
      :linenos:
      :emphasize-lines: 2
      
      [Session]
      key_phase = "e14389---sample-do-not-copy-me----e14389"

acl_group
---------

   Defines what default ACL groups should be assigned on new session creation. It receives a comma-delimited list of ACL groups.

   .. code-block:: ini
      :caption: Session default acl groups for guest
      :linenos:
      :emphasize-lines: 2,3
      
      [Session]
      acl_group.default_guest = "GUEST,PUBLIC"
      acl_group.default_user  = "USER,PUBLIC"
