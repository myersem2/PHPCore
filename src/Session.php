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
     * Session Meta Data
     *
     * @var array 
     */
    protected $sessionMetaData = null;

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
        return parent::destroy($id);
    }
    public function flushAll(): bool
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
    public function gc(int $max_lifetime): int|false
    {
        return parent::gc($max_lifetime);
    }
    public function giveAccess(string|array $acl_group): void
    {
        $acl_groups = is_string($acl_group) ? [$acl_group] : $acl_group;
        foreach ($acl_groups as $acl_group) {
            if ( ! in_array($acl_group, $this->sessionMetaData['acl_groups']) ) {
                $this->sessionMetaData['acl_groups'][] = $acl_group;
            }
        }
    }
    public function getAllKeys(): array
    {
        $handler = $this->_getHandler();
        switch ($this->Config['save_handler']) {
            case 'memcached':
                return $handler->getAllKeys();
            break;
            default:
                $save_handler = $this->Config['save_handler'];
                throw new Exception("Session::flushAll() is not supported for the '$save_handler' save handler");
            break;
        }
    }
    public function getFlash(string $key): mixed
    {
        return $this->FlashData['old'][$key] ?? null;
    }
    public function getMetaData(): array
    {
        return $this->sessionMetaData;
    }
    public function hasAccess(string $acl_group): bool
    {
        return in_array($acl_group, $this->sessionMetaData['acl_groups']);
    }
    public function isLoggedIn(): bool
    {
        $user_ident = $this->Config['user_identifier'];
        return (empty($this->sessionMetaData[$user_ident]) === false);
    }
    public function keepFlash(string $key): bool
    {
        if (isset($this->FlashData['old'][$key]) === false) {
            return false;
        }
        $this->FlashData['new'][$key] = $this->FlashData['old'][$key];
        return true;
    }
    public function loggedIn(int $user_ident_value): void
    {
        $user_ident = $this->Config['user_identifier'];
        $this->sessionMetaData[$user_ident] = $user_ident_value;
        $this->sessionMetaData['started'] = time();
    }
    public function loggedOut(bool $keep_data = false): void
    {
        $user_ident = $this->Config['user_identifier'];
        $this->sessionMetaData[$user_ident] = null;
        if ($keep_data) {
            $this->sessionMetaData['started'] = time();
        } else {
            $this->sessionMetaData['started'] = time();
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

        $default = function() {
            $acl_groups = explode(',', $this->Config['default_acl_groups']);
            $user_ident = $this->Config['user_identifier'];
            $method = isset($_COOKIE[session_name()]) ? 'cookie' : null;
            $method = (getenv(session_name()) !== false) ? 'environment' : $method;
            return [
                'started'    => time(),
                $user_ident  => null,
                'acl_groups' => $acl_groups,
                'method'     => $method,
                'updated'    => time(),
            ];
        };

        $clean_data = unserialize($data);

        if (isset($clean_data['_META'])) {
            $this->sessionMetaData = $clean_data['_META'];
            unset($clean_data['_META']);
        } else {
            $this->sessionMetaData = $default();
        }

        if (isset($clean_data['_FLASH'])) {
            $this->FlashData['old'] = $clean_data['_FLASH'];
            unset($clean_data['_FLASH']);
        }

        $data = serialize($clean_data);

        $this->sessionMetaData['timeleft'] = null;
        $gc_maxlength = $this->Config['gc_maxlength'] ?? null;
        if (empty($gc_maxlength) === false) {
            $timeleft = $this->sessionMetaData['started'] + intval($gc_maxlength) - time();
            $this->sessionMetaData['timeleft'] = $timeleft;
            if ($timeleft < 0) {
                $this->sessionMetaData = $default();
                $data = '';
                $this->sessionMetaData['timeleft'] = 0;
            }
        }

        return $data;
    }
    public function setFlash(string $key, mixed $value): void
    {
        $this->FlashData['new'][$key] = $value;
    }
    public function write(string $id, string $data): bool
    {
        $this->sessionMetaData['updated'] = time();
        $clean_data = unserialize($data);
        $clean_data['_META'] = $this->sessionMetaData;

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
