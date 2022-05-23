<?php  declare(strict_types=1);
/**
 * PHPCore - Database
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

use \PDO;
use \PDOException;
use \Exception;

// -------------------------------------------------------------------------------------------------

/**
 * Database Class
 *
 * This class is used to interface with a database using a PDO driver.
 *
 * @see https://manual.phpcore.org/class/database
 */
final class Database
{
    /**
     * PDO default options
     *
     * @cont array
     */
    const OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    /**
     * Constant flags
     *
     * @const int
     */
    const RETURN_DEBUG              = 1;    // Return array with debug information
    const THROW_ON_ERROR            = 2;    // Throw error
    const USE_ROWSETS               = 4;    // Statment consits of mutiple rowsets
    const MERGED_ROWSETS            = 8;    // Merge all rowsets into one rowset
    const RETURN_BOOL               = 16;   // Return boolean value if statment was successfuly
    const RETURN_ROWS_AFFECTED      = 32;   // Returns integer of rows affected
    const RETURN_LAST_INSERT_ID     = 64;   // Returns last insert id
    const RETURN_FIRST_RESULT_ONLY  = 128;  // Return first row only
    const INSERT_IGNORE             = 256;  // Use IGNORE modifier for the INSERT statement
    const ON_DUPLICATE_UPDATE       = 512;  // Use ON DUPLICATE UPDATE for INSERT statement
    const DISABLE_PLACEHOLDERS      = 1024; // Do not user statment placeholders, USE WITH CAUTION

    // -----------------------------------------------------------------------------------------

    /**
     * Instance that have been created
     *
     * @var array
     */
    protected static $Instances = [];

    // -----------------------------------------------------------------------------------------

    /**
     * Database config
     *
     * @var array   
     */
    protected $Config = [];

    /**
     * This PDO handler
     *
     * @var object   
     */
    protected $Handler = null;

    /**
     * Last Statment Insert ID
     *
     * @var int 
     */
    protected $LastInsertId = 0;

    /**
     * Last Statment Rows Affected
     *
     * @var int 
     */
    protected $RowsAffected = 0;

    // -----------------------------------------------------------------------------------------

    /**
     * Get Instance
     *
     * This method is used to get an instance that has already been constructed.
     *
     * @param string $name Name of instance
     *
     * @throws Exception If instance is not found
     * @return object
     */
    public static function &getInstance(string $name = 'main')
    {
        if (empty(self::$Instances[$name]) === true) {
            // TODO: Load from config and 
            throw new Exception(
                "The instance $name could not be found or has not been constructed."
            );
        }
        return self::$Instances[$name];
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @see https://www.php.net/manual/en/ref.pdo-mysql.connection.php
     *
     * @param string $dsn Data Source Name (DSN)
     * @param string $usr The user name for the DSN string
     * @param string $pwd The password for the DSN string
     * @param string $name The name to store this instance under
     *
     * @throws Exception If instance is already constructed
     * @throws PDOException(...) Various
     */
    public function __construct(string $dsn = null, string $usr = null, string $pwd = null, string $name = 'main')
    {
        if (empty(self::$Instances[$name]) === false) {
            throw new Exception("Databse instance with the name $name has already been constructed");
        }
        try {
            $this->Handler = new PDO($dsn, $usr, $pwd, Database::OPTIONS);
            $this->Config['dsn'] = parse_dsn($dsn);
            self::$Instances[$name] =& $this;
        } catch (PDOException $err) {
            throw new PDOException($err->getMessage(), intval($err->getCode()));
        }
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Create Record
     *
     * This method is used to create a new record in the database. If the flag
     * ON_DUPLICATE_UPDATE is used then you can update a record if a duplicate is
     * found using the $dupUpdate parameter.
     *
     * @param string $table Table name the record will be created in
     * @param array $data Array consiting of data to create record from
     * @param int $flags Bitwise flags for this method
     * @param array $dupUpdate Array consiting of update criteria if duplicate
     *
     * @throws Exception(4000) ON_DUPLICATE_UPDATE flag used without $dupUpdate
     * @throws Exception(...) Various (if flag THROW_ON_ERROR)
     * @return int | bool
     */
    public function createRecord(string $table, array $data, int $flags = 0, array $dupUpdate = null): mixed
    {
        // TODO: build
        return false;
    }

    /**
     * Get Last Insert ID
     *
     * Returns the ID of the last inserted row, or the last value from a
     * sequence object, depending on the underlying driver.
     */
    public function getLastInsertId(): int
    {
        return $this->LastInsertId;
    }

    /**
     * Get Rows Affected
     *
     * This method returns the number of rows affected by the last statement
     * executed.
     */
    public function getRowsAffected(): int
    {
        return $this->RowsAffected;
    }

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
