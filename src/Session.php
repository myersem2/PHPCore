<?php declare(strict_types=1);
/**
 * PHPCore - Session
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

use \Memcached;
use \Exception;

// -------------------------------------------------------------------------------------------------

/**
 * Session Class
 *
 * The Session class is a special class that has been used to extend the
 * built-in PHP SessionHandler for handling sessions. There are seven methods
 * which wrap the seven internal session save handler callbacks (open, close,
 * read, write, destroy, gc and create_sid). By default, this class will wrap
 * whatever internal save handler is set as defined by the session.save_handler
 * configuration directive which is usually files by default. Other internal
 * session save handlers are provided by PHP extensions such as SQLite
 * (as sqlite), Memcache (as memcache), and Memcached (as memcached).
 *
 * @seealso `PHP Session Functions`_ - Base PHP internal session functions that
 *          interface directly with this class.
 * @seealso `PHP SessionHandler Class`_ - Documentation for PHP internal
 *          SessionHandler Class.
 * @seealso `PHPCore Session Feature`_ - The PHPCore extended session handling
 *          features.
 * @seealso `PHPCore Session Functions`_ - The PHPCore session handling
 *          functions.
 */
final class Session extends \SessionHandler implements \SessionHandlerInterface, \SessionIdInterface
{
    use Core;

    /**
     * The name for the default instance for this class
     */
    public const DEFAULT_INSTANCE_NAME = 'main';

    // -----------------------------------------------------------------------------------------

    /**
     * Expire Data
     *
     * @var array   
     */
    protected array $ExpireData = [];

    /**
     * Flash Data
     *
     * @var array   
     */
    protected array $FlashData = [];
  
    /**
     * Metadata
     *
     * @var array 
     */
    protected ?array $Metadata = null;

    // -----------------------------------------------------------------------------------------

    /**
     * Constructor
     *
     * This constructor connects this class instance to the PHP built-in session handler via the
     * **session_set_save_handler()** and starts the session. If the session instance already exists an
     * **Exception** will be thrown.
     *
     * @throws Exception If instance is already constructed
     */
    public function __construct()
    {
        $name = self::DEFAULT_INSTANCE_NAME;
        if (isset(static::$Instances[$name])) {
            throw new Exception('Session instance has already been constructed');
        }

        // Instance and map
        self::initialize();
        self::$Instances[$name] =& $this;

        $this->Config = core_ini_get_all('Session');

        $session_ini_overrides = [
            'save_handler', 'save_path', 'name', 'cookie_lifetime', 'cookie_path',
            'cookie_domain', 'cookie_httponly', 'cookie_samesite', 'gc_probability',
            'gc_divisor', 'gc_maxlifetime'
        ];
        foreach ($session_ini_overrides as $key) {
            if (isset($this->Config[$key])) {
                ini_set("session.$key", $this->Config[$key]);
            } else {
                $this->Config[$key] = ini_get("session.$key");
            }
        }
        // Required due to "php" option not using serialize() and unserialize()
        // function resulting in issues with offset errors
        ini_set('session.serialize_handler', 'php_serialize');

        session_set_save_handler(static::$Instances[$name], true);

        // Get Session ID
        $session_id = $_COOKIE[session_name()] ?? null;
        $session_id = (getenv(session_name()) !== false) ? getenv(session_name()) : $session_id;

        // Start Session
        session_id($session_id);
        session_start();
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Close the session
     *
     * Closes the current session. Called internally by PHP SessionHandler.
     *
     * @see https://www.php.net/manual/en/sessionhandler.close.php
     *
     * @return boolean The return value (usually true on success, false on failure). Note this value
     *                 is returned internally to PHP for processing.
     */
    public function close(): bool
    {
      return parent::close();
    }

    /**
     * Return a new session ID
     *
     * Generates and returns a new session ID. Called internally by PHP SessionHandler.
     *
     * @see https://www.php.net/manual/en/sessionhandler.create-sid.php
     *
     * @return string A session ID valid for the default session handler.
     */
    public function create_sid(): string
    {
      return parent::create_sid();
    }

    /**
     * Destroy a session
     *
     * Destroys a session. Called internally by PHP SessionHandler. In addition to the
     * built-in function PHP provides this method will also clear the ``FlashData`` and reset the
     * session ``Metadata``. This method should normally be invoked by calling the
     * **session_destroy()** function.
     *
     * @see https://www.php.net/manual/en/sessionhandler.destroy.php
     *
     * @param string $id The session ID being destroyed
     * @return boolean The return value (usually true on success, false on failure). Note this value
     *                is returned internally to PHP for processing.
     */
    public function destroy(string $id): bool
    {
        $this->FlashData  = [];
        $this->ExpireData = [];
        $this->Metadata = $this->_defaultMetadata();
        unset($_SESSION['_META']);
        unset($_SESSION['_FLASH']);
        $cookie_autodestroy = $this->Config['cookie_autodestroy'] ?? false;
        if ($cookie_autodestroy) {
            delcookie(ini_get('session.name'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
        }
        return parent::destroy($id);
    }

    /**
     * Destroy all sessions
     *
     * Destroys **ALL** sessions if the save handlers supports this method.
     *
     * @throws Exception If save handler does not support this method.
     * @return boolean Returns true on success or false on failure.
     */
    public function destroyAll(): bool
    {
        $handler = $this->_getHandler();
        switch ($this->Config['save_handler']) {
            case 'memcached':
                $cookie_autodestroy = $this->Config['cookie_autodestroy'] ?? false;
                if ($cookie_autodestroy) {
                    delcookie(ini_get('session.name'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
                }
                return $handler->flush();
            break;
            default:
                $this->_methodNotSupported(__METHOD__);
            break;
        }
    }

    /**
     * Get session flash data item
     *
     * This method will return the flash data item that matches the provided
     * key. If a key is not provided the entire flash data array will be
     * returned.
     *
     * @param string $key The key of the flash data item to retrieve
     * @return mixed Returns the flash data item
     */
    public function flashGet(?string $key = null): mixed
    {
        if ($key === null) {
          return $this->FlashData['old'] ?? [];
        }
        return $this->FlashData['old'][$key] ?? null;
    }

    /**
     * Keep session flash data item
     *
     * This method will keep a session flash data item for the next session.
     *
     * @param string $key The key of the flash data item to save
     * @return bool Return true on success and false if not found
     */
    public function flashKeep(string $key): bool
    {
        if (isset($this->FlashData['old'][$key]) === false) {
            return false;
        }
        $this->FlashData['new'][$key] = $this->FlashData['old'][$key];
        return true;
    }
    
    /**
     * Set session flash data item
     *
     * This method will set a session flash data item to be used for the
     * next session.
     *
     * @param string $key The key of the flash data item
     * @param mixed $value The value of the flash data item
     * @return void
     */
    public function flashSet(string $key, mixed $value): void
    {
        $this->FlashData['new'][$key] = $value;
    }

    /**
     * Get session data item
     *
     * This method is used to retrieve a session data item.
     *
     * @param string $key Key of session data item to retrieve
     * @return mixed Data item from session data
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Get all sessions
     *
     * This method will get all sessions from the handler. Not all save handlers
     * support this method. If it is not supported then an empty array will be
     * returned.
     *
     * @throws Exception If save handler does not support this method.
     * @return array Sessions from handler
     */
    public function getAllSessions(): array
    {
        $handler = $this->_getHandler();
        $session_ids = [];
        switch ($this->Config['save_handler']) {
            case 'memcached':
                $prefix = $this->Config['save_handler_id_prefix'];
                foreach ($handler->getAllKeys() as $cached_key) {
                    if (str_starts_with($cached_key, $prefix)) {
                        $session_ids[] = substr($cached_key, strlen($prefix));
                    }
                }
            break;
            default:
                $this->_methodNotSupported(__METHOD__);
            break;
        }
        return $session_ids;
    }

    /**
     * Get session metadata
     *
     * This method will return metadata with a provided key. If no key is passed
     * the entire metadata array will be returned.
     *
     * @param string $key Metadata Key
     * @return mixed Session Metadata
     */
    public function getMetadata(?string $key = null): mixed
    {
        if ($key !== null) {
            return $this->Metadata[$key] ?? null;
        }
        return $this->Metadata;
    }

    /**
     * Cleanup old sessions
     *
     * Cleans up expired sessions. Called internally by PHP SessionHandler.
     *
     * @see https://www.php.net/manual/en/sessionhandler.gc.php
     *
     * @return int|false Returns the number of deleted sessions on success, or
     *                   false on failure.
     */
    public function gc(int $max_lifetime): int|false
    {
        switch ($this->Config['save_handler']) {
            case 'memcached':
              return parent::gc($max_lifetime);
            break;
            default:
                // Ignore non supporting save handlers
            break;
        }
    }

    /**
     * Initialize session
     *
     * Create new session, or re-initialize existing session. Called internally
     * by PHP when a session starts either automatically or when session_start()
     * is invoked.
     *
     * @see https://www.php.net/manual/en/sessionhandler.open.php
     *
     * @return boolean The return value (usually true on success, false on failure).
     *                 Note this value is returned internally to PHP for processing.
     */
    public function open(string $path, string $name): bool
    {
        return parent::open($path, $name);
    }

    /**
     * Read session data
     *
     * Reads the session data from the session storage, and returns the result back to PHP for
     * internal processing. This method is called automatically by PHP when a session is started
     * (either automatically or explicitly with session_start() and is preceded by an internal call
     * to the Session::open().
     *
     * @param string $id The session id to read data for.
     * @return string|false Returns an encoded string of the read data. If nothing was read, it must
     *                      return false. Note this value is returned internally to PHP for processing.
     */
    public function read(string $id): string|false
    {
        $data = @parent::read($id); // may throw warning if session locked

        if (empty($data) === false and $this->Config['encrypt']) {
            $key_phase = $this->Config['key_phase'];
            $data = $this->_decrypt($data, $key_phase);
        }

        $clean_data = empty($data) ? [] : unserialize($data);

        if (isset($clean_data['_META'])) {
            $this->Metadata = $clean_data['_META'];
        } else {
            $clean_data['_META'] = $this->Metadata = $this->_defaultMetadata();
        }

        if (isset($clean_data['_FLASH'])) {
            $clean_data['_FLASH'] = $this->FlashData['old'] = $clean_data['_FLASH'];
        }

        if (isset($clean_data['_EXP'])) {
            $this->ExpireData = $clean_data['_EXP'];
        } else {
            $this->ExpireData = [];
        }
        $ExpireData = [];
        foreach ($this->ExpireData as $key=>$ttl) {
            if ($ttl < time()) {
                unset($clean_data[$key]);
            } else {
              $ExpireData[$key] = $ttl;
            }
        }
        $this->ExpireData = $ExpireData;

        $this->Metadata['TimeLeft'] = null;
        $gc_maxlength = $this->Config['gc_maxlength'] ?? null;
        if (empty($gc_maxlength) === false) {
            $timeleft = $this->Metadata['Started'] + intval($gc_maxlength) - time();
            $this->Metadata['TimeLeft'] = $timeleft;
            if ($timeleft < 0) {
                $this->ExpireData = [];
                $this->Metadata = $this->_defaultMetadata();
                $this->Metadata['TimeLeft'] = intval($gc_maxlength);
                $clean_data = [];
            }
            $clean_data['_META']['TimeLeft'] = $this->Metadata['TimeLeft'];
        }

        $data = serialize($clean_data);
        return $data;
    }

    /**
     * Set session data item
     *
     * This method is used to store a session data item. If the optional ``$ttl``
     * is passed the data item will also be given an expiration.
     *
     * @param string $key Key of session data item to set.
     * @param mixed $value Value of session data item to set.
     * @param integer $ttl Time To Live for this data item.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $_SESSION[$key] = $value;
        if ($ttl !== null) {
            $this->ExpireData[$key] = time() + $ttl;
        } else {
            unset($this->ExpireData[$key]);
        }
    }

    /**
     * Set the session metadata
     *
     * This method is used to store a session metadata data item.
     *
     * @param string $key Key of session data item to set.
     * @param mixed $value Value of session data item to set.
     */
    public function setMetadata(string $key, mixed $value): void
    {
        switch ($key) {
            case 'UserTempRoles':
                if ( ! is_array($value)) {
                    throw new Exception("session metadata item UserTempRoles MUST be an array");
                }
                $this->Metadata['UserTempRoles'] = $value;
            break;

            case 'UserId':
                $this->Metadata['UserId'] = empty($user_id) ? null : intVal($user_id);
                $this->Metadata['UserTempRoles'] = [];
            break;

            case 'Started':
            case 'SessionId':
            case 'TimeLeft':
            case 'Updated':
                throw new Exception("$key cannot be set manually");
            break;

            default:
                $this->Metadata[$key] = $value;
            break;
        }

    }

    /**
     * Write session data
     *
     * Writes the session data to the session storage. Called by normal PHP shutdown, by session_write_close(),
     * or when session_register_shutdown() fails. PHP will call SessionHandler::close() immediately after this
     * method returns.
     *
     * @param string $id The session id
     * @param string $data The encoded session data. This data is the result of the PHP internally encoding the
     *                     $_SESSION super global to a serialized string and passing it as this parameter.
     *                     Please note sessions use an alternative serialization method.
     * @return boolean The return value (usually true on success, false on failure). Note this value is
     *                 returned internally to PHP for processing.
     */
    public function write(string $id, string $data): bool
    {
        $clean_data = unserialize($data);
        $this->Metadata['Updated'] = time();
        $clean_data['_META'] = $this->Metadata;

        unset($clean_data['_FLASH']);
        if (isset($this->FlashData['new'])) {
            $clean_data['_FLASH'] = $this->FlashData['new'];
        }

        if (empty($this->ExpireData) === false) {
          $clean_data['_EXP'] = $this->ExpireData;
        } else {
          unset($clean_data['_EXP']);
        }

        $data = serialize($clean_data);

        $key_phase = $this->Config['key_phase'];
        if (empty($data) === false and $this->Config['encrypt']) {
            $data = $this->_encrypt($data, $key_phase);
        }

        return parent::write($id, $data);
    }

    // -----------------------------------------------------------------------------------------

    private function _defaultMetadata(): array
    {
        return [
            'UserTempRoles' => [],
            'Started'       => time(),
            'SessionId'     => session_id(),
            'TimeLeft'      => null,
            'Updated'       => time(),
            'UserId'        => null,
        ];
    }
    private function _decrypt(string $data, string $key_phase): string
    {
        $data = base64_decode($data);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);
        $rounds = 3; // depends on key length
        $data00 = $key_phase.$salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);

        return openssl_decrypt($ct, 'AES-256-CBC', $key, 0, $iv);
    }
    private function _encrypt(string $data, string $key_phase): string
    {
        $salt = openssl_random_pseudo_bytes(16);
        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx.$key_phase.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($salt . $encrypted_data);
    }
    private function &_getHandler(): object|null
    {
        
        if (isset($this->Handler)) {
            return $this->Handler;
        }
        switch ($this->Config['save_handler']) {
            case 'memcached':
                $this->Handler = new Memcached();
                $servers = explode(',', $this->Config['save_path']);
                foreach ($servers as $server) {
                    list($host, $port) = explode(':', $server);
                    $this->Handler->addServer($host, intval($port));
                }
            break;
        }
        return $this->Handler;
    }
    private function _methodNotSupported(string $method): void
    {
        $hdlr = $this->Config['save_handler'];
        throw new Exception("Session::$method() is not supported for the '$hdlr' save handler");
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
