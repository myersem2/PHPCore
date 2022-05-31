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
use \SessionHandler;
use \SessionHandlerInterface;
use \SessionIdInterface;

// -------------------------------------------------------------------------------------------------

/**
 * Session Class
 *
 * This class is used to interface with a session using a session driver.
 *
 * @see https://manual.phpcore.org/class/session
 */
final class Session extends SessionHandler implements SessionHandlerInterface, SessionIdInterface
{
    /**
     * Handler that have been created
     *
     * @var object
     */
    protected static $Handler = null;

    /**
     * Instance that have been created
     *
     * @var object
     */
    protected static $Instance = null;

    // -----------------------------------------------------------------------------------------

    /**
     * Database config
     *
     * @var array   
     */
    protected $Config = [];

    /**
     * Flash Database
     *
     * @var array   
     */
    public $FlashData = [];

    /**
     * Session Metadata
     *
     * @var array 
     */
    protected $sessionMetadata = null;

    // -----------------------------------------------------------------------------------------

    /**
     * This method is really just used for testing
     */
    public static function unlinkInstances(): void
    {
        self::$Instance = null;
    }

    /**
     * Get Instance
     *
     * This method is used to get the instance that has already been
     8 constructed.
     *
     * @return object
     */
    public static function &getInstance(): object
    {
        if (empty(self::$Instance) === true) {
            self::$Instance = new self();
        }
        return self::$Instance;
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @throws Exception If instance is already constructed
     */
    public function __construct()
    {
        if (isset(self::$Instance)) {
            throw new Exception("Session instance has already been constructed.");
        }

        self::$Instance =& $this;
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
        // function resulting in issues with offset erors
        ini_set('session.serialize_handler', 'php_serialize');

        session_set_save_handler(self::$Instance, true);

        // Get Session ID
        $session_id = $_COOKIE[session_name()] ?? null;
        $session_id = (getenv(session_name()) !== false) ? getenv(session_name()) : $session_id;
        session_id($session_id);
        session_start();
    }
    
    
    public function close(): bool
    {
        return parent::close();
    }
    public function create_sid(): string
    {
        return parent::create_sid();
    }
    public function destroy(string $id): bool
    {
        $this->FlashData = [];
        $this->sessionMetadata = $this->defaultMetadata();
        unset($_SESSION['_META']);
        unset($_SESSION['_FLASH']);
        return parent::destroy($id);
    }


    /**
     * Destroy all session in handler.
     *
     * Note not all save handlers support this method.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function destroyAll(): bool
    {
        $handler = $this->_getHandler();
        switch ($this->Config['save_handler']) {
            case 'memcached':
                return $handler->flush();
            break;
            default:
                $save_handler = $this->Config['save_handler'];
                throw new Exception("Session::flushAll() is not supported for the '$save_handler' save handler");
            break;
        }
    }

    /**
     * Get session flash data item
     *
     * @param string $key The key of the flash data item to retrieve
     * @return mixed
     */
    public function flashGet(string $key): mixed
    {
        return $this->FlashData['old'][$key] ?? null;
    }

    /**
     * Keep session flash data item for the next session. Return true on success and
     * false if item was not found in flash data.
     *
     * @param string $key The key of the flash data item to retrieve
     * @return bool
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
     * Set session flash data item for use in the next session
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
     * Get all current sessions in handler.
     *
     * Note not all save handlers support this method.
     *
     * @return array
     */
    public function getAllSessions(): array
    {
        $handler = $this->_getHandler();
        switch ($this->Config['save_handler']) {
            case 'memcached':
                $session_ids = [];
                $prefix = $this->Config['save_handler_id_prefix'];
                foreach ($handler->getAllKeys() as $cached_key) {
                    if (str_starts_with($cached_key, $prefix)) {
                        $session_ids[] = substr($cached_key, strlen($prefix));
                    }
                }
                return $session_ids;
            break;
            default:
                $save_handler = $this->Config['save_handler'];
                throw new Exception("Session::flushAll() is not supported for the '$save_handler' save handler");
            break;
        }
    }


    public function gc(int $max_lifetime): int|false
    {
        return parent::gc($max_lifetime);
    }


    /**
     * Grant session access to an ACL group
     *
     * @param string|array $groups ACL group or array of ACL groups to be granted
     * @return void
     */
    public function grant(string|array $groups): void
    {
        $groups = is_string($groups) ? [$groups] : $groups;
        $acl_groups = array_merge($this->sessionMetadata['acl_groups'], $groups);
        $this->sessionMetadata['acl_groups'] = array_unique($acl_groups);
    }

    /**
     * Revoke session access to an ACL group
     *
     * @param string|array $groups ACL group or array of ACL groups to be revoked
     * @return void
     */
    public function revoke(string|array $groups): void
    {
        $groups = is_string($groups) ? [$groups] : $groups;
        $filtered_acl_groups = array_filter($this->sessionMetadata['acl_groups'], function($group) use(&$groups) {
            return ! in_array($group, $groups);
        });
        $this->sessionMetadata['acl_groups'] = $filtered_acl_groups;
    }

    /**
     * Returns all the session metadata
     *
     * If no key is passed the entire metadata array will be returned.
     *
     * @param string $key Metadata Key
     * @return array Session Metadata
     */
    public function getMetadata(string $key = null): mixed
    {
        if ($key !== null) {
            return $this->sessionMetadata[$key] ?? null;
        }
        return $this->sessionMetadata;
    }

    /**
     * Check if session has ACL group
     *
     * @param string $group ACL group to check access for
     * @return boolean
     */
    public function hasAccess(string $group): bool
    {
        return in_array($group, $this->sessionMetadata['acl_groups']);
    }

    /**
     * Bind the current session user
     *
     * Note the acl_groups will be replaced with the ones declared in the
     * acl_group.default_user directive. The session start time will also be
     * reset.
     *
     * @param string|int $user The user to bind to the current session
     * @return void
     */
    public function userBind(int|string $user): void
    {
        $this->sessionMetadata['user'] = $user;
        $this->sessionMetadata['started'] = time();
        $this->sessionMetadata['acl_groups'] = explode(',', $this->Config['acl_group.default_user']);
    }

    /**
     * Unbind the current session user
     *
     * Note the acl_groups will be replaced with the ones declared in the
     * acl_group.default_guest directive. The session start time will also be
     * reset.
     *
     * @param boolean $keep_data Keep the session data
     * @return void
     */
    public function userUnbind(bool $keep_data = false): void
    {
        $this->sessionMetadata['user']    = null;
        $this->sessionMetadata['started'] = time();
        $this->sessionMetadata['acl_groups'] = explode(',', $this->Config['acl_group.default_guest']);
        if ( ! $keep_data ) {
            $_SESSION = [];
        }
    }

    public function open(string $path, string $name): bool
    {
        return parent::open($path, $name);;
    }
    public function read(string $id): string|false
    {
        try {
            $data = parent::read($id);
        } catch (ErrorException $e) {
            // TODO: Handel Locked session (last session did not close/write
            //       $e->getMessage() = "Unable to clear session lock record"
            //       $e->getCode() = 2
            die('NEED TO FIX THIS');
        }

        if (empty($data) === false and $this->Config['encrypt']) {
            $key_phase = $this->Config['key_phase'];
            $data = $this->_decrypt($data, $key_phase);
        }
        $clean_data = unserialize($data);

        if (isset($clean_data['_META'])) {
            $this->sessionMetadata = $clean_data['_META'];
        } else {
            $clean_data['_META'] = $this->sessionMetadata = $this->defaultMetadata();
        }

        if (isset($clean_data['_FLASH'])) {
            $clean_data['_FLASH'] = $this->FlashData['old'] = $clean_data['_FLASH'];
        }

        $this->sessionMetadata['timeleft'] = null;
        $gc_maxlength = $this->Config['gc_maxlength'] ?? null;
        if (empty($gc_maxlength) === false) {
            $timeleft = $this->sessionMetadata['started'] + intval($gc_maxlength) - time();
            $this->sessionMetadata['timeleft'] = $timeleft;
            if ($timeleft < 0) {
                $this->sessionMetadata = $this->defaultMetadata();
                $data = '';
                $this->sessionMetadata['timeleft'] = 0;
            }
            $clean_data['_META']['timeleft'] = $this->sessionMetadata['timeleft'];
        }

        $data = serialize($clean_data);
        return $data;
    }

    /**
     * Returns time left in session
     * 
     * If the gc_maxlength directive is set it will return the difference in time
     * since the session started. If directive is not used will return null.
     *
     * @return integer|null
     */
    public function timeLeft(): int|null
    {
        return $this->getMetadata()['timeleft'];
    }

    public function write(string $id, string $data): bool
    {
        $clean_data = unserialize($data);
        $this->sessionMetadata['updated'] = time();
        $clean_data['_META'] = $this->sessionMetadata;

        unset($clean_data['_FLASH']);
        if (isset($this->FlashData['new'])) {
            $clean_data['_FLASH'] = $this->FlashData['new'];
        }

        $data = serialize($clean_data);

        $key_phase = $this->Config['key_phase'];
        if (empty($data) === false and $this->Config['encrypt']) {
            $data = $this->_encrypt($data, $key_phase);
        }

        return parent::write($id, $data);
    }

    // -----------------------------------------------------------------------------------------

    private function defaultMetadata(): array
    {
        $acl_groups = explode(',', $this->Config['acl_group.default_guest']);
        $method = isset($_COOKIE[session_name()]) ? 'cookie' : null;
        $method = (getenv(session_name()) !== false) ? 'environment' : $method;
        return [
            'acl_groups' => $acl_groups,
            'method'     => $method,
            'started'    => time(),
            'session_id' => session_id(),
            'timeleft'   => null,
            'updated'    => time(),
            'user'       => null,
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
        
        if (isset(self::$Handler)) {
            return self::$Handler;
        }
        switch ($this->Config['save_handler']) {
            case 'memcached':
                self::$Handler = new Memcached();
                $servers = explode(',', $this->Config['save_path']);
                foreach ($servers as $server) {
                    list($host, $port) = explode(':', $server);
                    self::$Handler->addServer($host, intval($port));
                }
            break;
        }
        return self::$Handler;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
