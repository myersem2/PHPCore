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
     * Constant flags
     *
     * @const int
     */
    const DEBUG_MODE                = 1;    // Return array with debug information

    // -----------------------------------------------------------------------------------------

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
     * Data
     *
     * @var array 
     */
    protected $Data = null;

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

        foreach (['save_handler', 'save_path', 'name'] as $key) {
            ini_set("session.$key", $this->Config[$key]);
        }

        session_set_save_handler(self::$Instance, true);

        // TODO: find session key vie $_COOKIE, $_ENV, ...
        session_id('vi5re08mrh9qbe1di1obnov316');
        // TODO: look for auto start and move to bootstrap
        session_start();

        $_SESSION['test'] = 'SAT ENSURE OF 32 CHARS';

    }

    public function close(): bool
    {
        $result = parent::close();
        $result_str = ($result) ? 'TRUE' : 'FALSE';
        echo "\n".str_color('close', 'red')."() => $result_str\n";
        return $result;
    }
    public function create_sid(): string
    {
        $result = parent::create_sid();
        echo "\n".str_color('create_sid', 'yellow')."() => $result\n";
        return $result;
    }
    public function destroy(string $id): bool
    {
        $result = parent::destroy($id);
        $result_str = ($result) ? 'TRUE' : 'FALSE';
        echo "\n".str_color('destroy', 'red')."($id) => $result_str\n";
        return $result;
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
        $result = parent::gc($max_lifetime);
        $result_str = ($result === FALSE) ? 'FALSE' : $result;
        echo "\n".str_color('gc', 'light_grey')."($max_lifetime) => $result_str\n";
        return $result;
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
    public function open(string $path, string $name): bool
    {
        $result = parent::open($path, $name);
        $result_str = ($result) ? 'TRUE' : 'FALSE';
        echo "\n".str_color('open', 'green')."($path, $name) => $result_str\n";
        return $result;
    }
    public function read(string $id): string|false
    {
        try {
            $data = parent::read($id);
        } Catch (Exception $e) {
            // TODO: Handel Locked session (last session did not close/write
            //       $e->getMessage() = "Unable to clear session lock record"
            //       $e->getCode() = 2
            return '';
        }
        if (empty($data) === false and $this->Config['encrypt']) {
            $key_phase = $this->Config['key_phase'];
            $data = $this->_decrypt($data, $key_phase);
        }
        $result_str = ($data === false) ? 'FALSE' : $data;
        echo "\n".str_color('read', 'light_blue')."($id) => $result_str\n";
        return $data;
    }
    public function write(string $id, string $data): bool
    {
        $key_phase = $this->Config['key_phase'];
        if (empty($data) === false and $this->Config['encrypt']) {
            $data = $this->_encrypt($data, $key_phase);
        }
        $result = parent::write($id, $data);
        $result_str = ($result) ? 'TRUE' : 'FALSE';
        echo "\n".str_color('write', 'light_magenta')."($id, $data) => $result_str\n";
        return $result;
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
