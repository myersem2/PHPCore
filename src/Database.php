<?php declare(strict_types=1);
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
    use Core;

    /**
     * The name for the default instance for this class
     */
    const DEFAULT_INSTANCE_NAME = 'main';

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
     * @cont int
     */
    const DEBUG_MODE                = 1;    // Return array with debug information
    const NO_THROW_ON_ERROR         = 2;    // Do not throw exception on error
    const USE_ROWSETS               = 4;    // Statement consist of multiple row sets
    const MERGED_ROWSETS            = 8;    // Merge all row sets into one row set
    const RETURN_BOOL               = 16;   // Return boolean value if statement was successful
    const RETURN_ROWS_AFFECTED      = 32;   // Returns integer of rows affected
    const RETURN_LAST_INSERT_ID     = 64;   // Returns last insert id
    const RETURN_FIRST_RESULT_ONLY  = 128;  // Return first row only
    const INSERT_IGNORE             = 256;  // Use IGNORE modifier for the INSERT statement
    const ON_DUPLICATE_UPDATE       = 512;  // Use ON DUPLICATE UPDATE for INSERT statement
    const DISABLE_PLACEHOLDERS      = 1024; // Do not user statement placeholders, USE WITH CAUTION

    // -----------------------------------------------------------------------------------------

    /**
     * Instance that have been created
     *
     * @var array
     */
    protected static $Instances = [];

    // -----------------------------------------------------------------------------------------

    /**
     * Debug Data
     *
     * @var array   
     */
    public $DebugData = [];

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
     * Last Exec Exception
     *
     * @var int 
     */
    protected $LastExecException = null;

    /**
     * Last Statement Insert ID
     *
     * @var int 
     */
    protected $LastInsertId = 0;

    /**
     * Last Statement Rows Affected
     *
     * @var int 
     */
    protected $RowsAffected = 0;

    // -----------------------------------------------------------------------------------------

    /**
     * This method is really just used for testing
     */
    public static function unlinkInstances(): void
    {
        self::$Instances = [];
    }

    /**
     * Get database class instance
     *
     * Returns the database instance for a given name name. If the database
     * handler has not been initiated yet it will be before the instance is
     * returned.
     *
     * If the **$name** is not provided the instance with the name 
     * Database::MAIN_INSTANCE_NAME
     *
     * @param string $name Name of instance
     * @throws Exception if instance is not found
     * @return object Database class instance
     */
    public static function &__getInstance(?string $name = null): object
    {
        if (empty(self::$Instances[$name])) {
            self::$Instances[$name] = new self($name);
        }
        return self::$Instances[$name];
    }

    // -----------------------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @see https://www.php.net/manual/en/ref.pdo-mysql.connection.php
     *
     * @param string $name The name to store this instance under
     * @param string $dsn Data Source Name (DSN)
     * @param string $usr The user name for the DSN string
     * @param string $pwd The password for the DSN string
     *
     * @throws Exception If instance is already constructed
     * @throws PDOException(...) Various
     */
    public function __construct(?string $name = null, string $dsn = '', string|null $usr = null, string|null $pwd = null)
    {
        $name = $name ?? self::MAIN_INSTANCE_NAME;
        if (isset(self::$Instances[$name])) {
            throw new Exception("Database instance with the name $name has already been constructed");
        }
        try {
            $this->Config = core_ini_get_all('Database');
            $pdo = [
                'dsn' => $dsn,
                'usr' => $usr,
                'pwd' => $pwd,
            ];
            if (empty($pdo['dsn']) or empty($pdo['usr']) or empty($pdo['pwd'])) {
                $connections = [
                    'main' => core_ini_get_all('Database', 'main')
                ];
                foreach (core_ini_get_all('Database') as $directive=>$value) {
                    if (preg_match('/^(alt[1-9])\.name/', $directive, $matches)) {
                        if (empty($connections[$value])) {
                            $connections[$value] = core_ini_get_all('Database', $matches[1]);
                        }
                    }
                }
                foreach (['dsn','usr','pwd'] as $key) {
                    if (empty($pdo[$key])) {
                        $pdo[$key] = $connections[$name][$key] ?? '';
                    }
                }
            }
            $this->Handler = new PDO($pdo['dsn'], $pdo['usr'], $pdo['pwd'], Database::OPTIONS);
            $this->Config['dsn'] = parse_dsn($pdo['dsn']);
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
     * NOTE: The RETURN_LAST_INSERT_ID is forced for this method
     *
     * @param string $table Table name the record will be created in
     * @param array $data Array consisting of data to create record from
     * @param array $dupUpdate Array consisting of update criteria if duplicate
     * @param int $flags Bitwise flags for this method
     * @param string $key Key for conflict on duplicate update
     *
     * @throws Exception(4000) Data cannot be empty
     * @throws Exception(4001) ON_DUPLICATE_UPDATE flag used without $dupUpdate
     * @return int | bool
     */
    public function createRecord(string $table, array $data, array|null $dupUpdate = null, int $flags = 0, string|null $key = null): int|bool
    {
        if (empty($data)) {
            throw new Exception(
                'Database::createRecord() requires $data parameter to not be empty',
                4001
            ); 
        }

        if ( ! ($flags & self::RETURN_BOOL) and ! ($flags & self::RETURN_LAST_INSERT_ID)) {
            $flags += self::RETURN_LAST_INSERT_ID;
        }

        return $this->createRecords($table, [$data], $dupUpdate, $flags, $key);
    }

    /**
     * Create Records
     *
     * This method is used to create multiple new records in the database. If the
     * flag ON_DUPLICATE_UPDATE is used then you can update a record if a
     * duplicate is found using the $dupUpdate parameter.
     *
     * NOTE: The RETURN_BOOL is forced for this method
     *
     * @param string $table Table name the record will be created in
     * @param array $data Array consisting of data to create record from
     * @param int $flags Bitwise flags for this method
     * @param array $dupUpdate Array consisting of update criteria if duplicate
     * @param string $key Key for conflict on duplicate update
     *
     * @throws Exception(4000) Records cannot be empty
     * @throws Exception(4001) ON_DUPLICATE_UPDATE flag used without $dupUpdate
     * @throws Exception(4002) $dupUpdate used without ON_DUPLICATE_UPDATE flag
     * @throws Exception(4003) Records have Inconsistent column counts
     * @throws Exception(4004) Key not passed with ON_DUPLICATE_UPDATE (sqlite)
     * @return int | bool
     */
    public function createRecords(string $table, array $records, array|null $dupUpdate = null, int $flags = 0, string|null $key = null): int|bool
    {
        if (empty($records) or empty($records[0])) {
            throw new Exception(
                'Database::createRecords() requires $records parameter to not be empty',
                4000
            ); 
        }

        if ( ! ($flags & self::RETURN_BOOL) and ! ($flags & self::RETURN_LAST_INSERT_ID)) {
            $flags += self::RETURN_BOOL;
        }

        $table = $this->_cleanName($table);
        $colsStr = $valsStr = $ignoreStr = $onDupUpdStr = '';
        $colsArr = $valsArr = $valueArr = $params = [];

        foreach ($records[0] as $column=>$value) {
            $colsArr[] = $this->_cleanName($column);
        }

        foreach ($records as $index=>$recordData) {
            if (count($recordData) !== count($colsArr)) {
                throw new Exception(
                    'Inconsistent column counts used for createRecords()',
                    4003
                );  
            }
            $valueArr = [];
            foreach ($recordData as $column=>$value) {
                $columnValName = 'v_'.$index.'_'.str_replace('.', '_', $this->_cleanName($column, false));
                $params[$columnValName] = $value;
                $valueArr[] = ":$columnValName";
            }
            $valsArr[] = '('.implode(', ', $valueArr).')';
        }

        $colsStr = implode(', ', $colsArr);
        $valsStr = implode("\n,", $valsArr);

        if ($flags & self::INSERT_IGNORE) {
            $ignoreStr = match($this->Config['dsn']['driver']) {
                'mysql'  => 'IGNORE',
                'sqlite' => 'OR IGNORE',
            };
        }

        if (isset($dupUpdate) and ! ($flags & self::ON_DUPLICATE_UPDATE)) {
            throw new Exception(
                'You must use the ON_DUPLICATE_UPDATE if dupUpdate parameter is passed for createRecords().',
                4001
            );
        }

        if (empty($dupUpdate) and ($flags & self::ON_DUPLICATE_UPDATE)) {
            throw new Exception(
                'dupUpdate parameter cannot be empty if ON_DUPLICATE_UPDATE flag is used for createRecords().',
                4002
            );
        }

        if ($flags & self::ON_DUPLICATE_UPDATE) {
            if ($this->Config['dsn']['driver'] === 'sqlite') {
                if (empty($key)) {
                    throw new Exception(
                        'key parameter cannot be empty if ON_DUPLICATE_UPDATE flag is used for createRecords() for this driver.',
                        4004
                    ); 
                }
                $keyInjSafe = $this->_cleanName($key);
            }

            foreach ($dupUpdate as $column=>$value) {
                $colUpdName = 'u_'.$this->_cleanName($column, false);
                $colInjSafe = $this->_cleanName($column);
                if (empty($onDupUpdStr)) {
                    $onDupUpdStr = match($this->Config['dsn']['driver']) {
                        'mysql'  => ' AS `new` ON DUPLICATE KEY UPDATE ',
                        'sqlite' => " ON CONFLICT($keyInjSafe) DO UPDATE SET ",
                    };
                } else {
                    $onDupUpdStr .= ', ';
                }
                $onDupUpdStr .= "$colInjSafe = :$colUpdName";
                $params[$colUpdName] = $value;
            }
        }

        return $this->_execStatement("INSERT $ignoreStr INTO $table ($colsStr) VALUES $valsStr $onDupUpdStr", $params, $flags);

    }

    public function deleteRecord(string $table, array $data, int $flags = 0): int
    {
        // TODO: build, will look up table schema and fill-in where array based on primary keys
    }

    public function deleteRecords(string $table, array $whereArr, int $flags = 0): int
    {
        $table = $this->_cleanName($table);
        $whereStr = '';
        $params = [];
        $this->_buildWhere($whereArr, $whereStr, $params);
        if ( ! ($flags & self::RETURN_ROWS_AFFECTED)) {
            $flags += self::RETURN_ROWS_AFFECTED;
        }
        return $this->_execStatement("DELETE FROM $table $whereStr", $params, $flags);
    }

    public function getRecord(string $table, array $whereArr = NULL, $flags = 0): null|object
    {
        $table = $this->_cleanName($table);
        $whereStr = '';
        $params = [];
        $this->_buildWhere($whereArr, $whereStr, $params);
        if ( ! ($flags & self::RETURN_FIRST_RESULT_ONLY)) {
            $flags += self::RETURN_FIRST_RESULT_ONLY;
        }
        return $this->_execStatement("SELECT * FROM $table $whereStr", $params, $flags);
    }

    public function getRecords(string $table, array $whereArr = NULL, array|null $orderBy = null, int|null $limit = null, int|null $offset = null, int $flags = 0): array
    {
        $table = $this->_cleanName($table);
        $whereStr = $orderByLimitStr = '';
        $params = [];
        $this->_buildWhere($whereArr, $whereStr, $params);
        $this->_buildOrderByLimit($orderBy, $orderByLimitStr, $limit, $offset);
        return $this->_execStatement("SELECT * FROM $table $whereStr $orderByLimitStr", $params, $flags);
    }

    public function getSchema(string|null $database = null): array
    {
        $database = $database ?? $this->Config['dsn']['dbname'];
        $this->query('
            SELECT *
             FROM `information_schema`.`TABLES` AS `s`
        LEFT JOIN `extended_schema`.`TABLES`    AS `x` USING (`TABLE_SCHEMA`,`TABLE_NAME`)
            WHERE `s`.`TABLE_SCHEMA` = :database
         ORDER BY `LIST_POSITION`  ASC
        ',[
            'database' => $database,
        ]);
    }

    public function getTableSchema(string $table, string|null $database = null): array
    {
        $database = $database ?? $this->Config['dsn']['dbname'];
        $this->query('
            SELECT *
             FROM `information_schema`.`COLUMNS` AS `s`
        LEFT JOIN `extended_schema`.`COLUMNS`    AS `x` USING (`TABLE_SCHEMA`,`TABLE_NAME`,`COLUMN_NAME`)
            WHERE `s`.`TABLE_SCHEMA` = :database
              AND `s`.`TABLE_NAME`   = :table
         ORDER BY `LIST_POSITION`  ASC
        ',[
            'database' => $database,
            'table'    => $table,
        ]);
    }

    public function getListFields_MOVE_TO_getTableSchema(string $table)
    {
        $structure = $this->getTableSchema($table);
        $fields = [];
        foreach ($structure as $column) {
            $field = (object)[
                'columnName'    => $column->COLUMN_NAME,
                'title'         => isset($column->TITLE) ? $column->TITLE : $column->COLUMN_NAME,
                'create'        => (empty($column->CREATE_HIDE) !== false),
                'key'           => ($column->COLUMN_KEY === 'PRI'),
                'list'          => (empty($column->LIST_HIDE) !== false),
                'listClass'     => $column->LIST_STYLE_CLASS,
                'defaultValue'  => $column->COLUMN_DEFAULT,
                'edit'          => (empty($column->EDIT_HIDE) !== false),
                'inputTitle'    => $column->INPUT_TITLE ?? null,
                'isNullable'    => ($column->IS_NULLABLE === 'YES'),
            ];
            if ($column->EXTRA === 'auto_increment') $field->create = false;
            switch ($column->DATA_TYPE) {
                case 'json':
                    if (isset($column->LIST_HIDE) === false) $field->list = false;
                break;
                case 'text':
                    if (isset($column->LIST_HIDE) === false) $field->list = false;
                break;
                case 'float':
                    if (isset($column->LIST_STYLE_CLASS) === false) $field->listClass = 'align-center';
                break;
                case 'int':
                    if (isset($column->LIST_STYLE_CLASS) === false) $field->listClass = 'align-center';
                break;
                case 'tinyint':
                    $field->type = 'checkbox';
                break;
                case 'enum':
                    $field->options = explode(',', substr(str_replace("','", ',', $column->COLUMN_TYPE), 6, -2));
                break;
            }
            $fields[$column->COLUMN_NAME] = $field;
        }
        return $fields;
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * This method executes an SQL string in a single function call,
     * returning the number of rows affected by the statement. This method is
     * basically a wrapper for the PDO::exec() method. This method 
     *
     * @see https://www.php.net/manual/en/pdo.exec.php
     *
     * @param string $sql SQL String to be used to create and execute statement from
     * @param array $params An array of values with as many elements as there
     *                      are bound parameters in the SQL statement being
     *                      executed. All values are treated as PDO::PARAM_STR.
     *
     * @return integer The number of rows affected by the last SQL statement
     */
    public function exec(string $sql, array $params = [], int $flags = 0): int
    {

        if ( ! ($flags & self::RETURN_ROWS_AFFECTED) and ! ($flags & self::RETURN_LAST_INSERT_ID)) {
            $flags += self::RETURN_ROWS_AFFECTED;
        }
    
        return $this->_execStatement($sql, $params, self::RETURN_ROWS_AFFECTED);
    }

    /**
     * Get Last Exec Exception
     *
     * Returns the PDO Exception of the -execsTatement().
     */
    public function getLastExecException(): object
    {
        return $this->LastExecException;
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

    public function procedure(string $procedure, array $variables = null, int $flags = 0)
    {
        $procedure = $this->_cleanName($procedure);
        $params = [];
        $paramStr = '';
        if (isset($variables)) {
            foreach ($variables as $index=>$value) {
                if (empty($paramStr) === false) {
                    $paramStr .= ', ';
                }
                $paramStr .= "?";
                $params[] = $value;
            }
        }
        return $this->_execStatement("CALL $procedure($paramStr);", $params, $flags);
    }

    public function query(string $sql, array $params = [], int $flags = 0): array|object
    {
        return $this->_execStatement($sql, $params, $flags);
    }

    public function updateRecord(string $table, array $data, int $flags = 0): int
    {
        // TODO: build, will look up table schema and fill-in where array based on primary keys
    }

    public function updateRecords(string $table, array $data, array $whereArr, int $flags = 0): int
    {
        $table = $this->_cleanName($table);
        $whereStr = $setStr = '';
        $params = [];
        $this->_buildWhere($whereArr, $whereStr, $params);
        $this->_buildSet($data, $setStr, $params);
        if ( ! ($flags & self::RETURN_ROWS_AFFECTED)) {
            $flags += self::RETURN_ROWS_AFFECTED;
        }
        return $this->_execStatement("UPDATE $table $setStr $whereStr", $params, $flags);
    }

    // -----------------------------------------------------------------------------------------

    protected function _buildOrderByLimit(array|null $orderByArr, string &$orderByLimitStr, int|null $limit = null, int|null $offset = null, int $flags = 0)
    {
        if (isset($orderByArr)) {
            foreach ($orderByArr as $item) {
                $itemParts = explode(' ', trim($item));
                $orderByDir = 'ASC';
                $column = $this->_cleanName($itemParts[0]);
                if (count($itemParts) > 1) {
                    $orderByDir = ($itemParts[1] == 'asc') ? 'ASC' : 'DESC';
                }
                $columnInjectionSafe = $this->_cleanName($column);
                if (empty($orderByLimitStr)) {
                    $orderByLimitStr = 'ORDER BY ';
                } else {
                    $orderByLimitStr .= ', ';
                }
                $orderByLimitStr .= "$columnInjectionSafe $orderByDir";
            }
        }
        if (isset($limit)) {
            $orderByLimitStr .= " LIMIT $limit";
            if (isset($offset)) {
                $orderByLimitStr .= " OFFSET $offset";
            }
        }
    }

    protected function _buildSet(array $setArr, string &$setStr, array &$params, int $flags = 0)
    {
        if (empty($setArr) === false) {
            foreach ($setArr as $column=>$value) {
                $columnInjectionSafe = $this->_cleanName($column);
                $columnSetName = 's_'.str_replace('.', '_', $this->_cleanName($column, false));
                if (empty($setStr)) {
                    $setStr = 'SET ';
                } else {
                    $setStr .= ', ';
                }
                $setStr .= "$columnInjectionSafe = :$columnSetName";
                $params[$columnSetName] = $value;
            }
        }
    }

    protected function _buildWhere(array $whereArr, string &$whereStr, array &$params, int $flags = 0): void
    {
        if (empty($whereArr) === false) {
            foreach ($whereArr as $column=>$value) {
                $columnInjectionSafe = $this->_cleanName($column);
                $columnWhereName = 'w_'.str_replace('.', '_', $this->_cleanName($column, false));
                if (empty($whereStr)) {
                    $whereStr = 'WHERE ';
                } else {
                    $whereStr .= ' AND ';
                }
                $whereStr .= "$columnInjectionSafe = :$columnWhereName";
                $params[$columnWhereName] = $value;
            }
        }
    }

    protected function _cleanName(string $name, bool $use_backtics = true)
    {
        $name = preg_replace("/[^A-Za-z0-9._]/", '', $name);
        if ($use_backtics ) {
            return '`'.str_replace('.', '`.`', $name).'`';
        } else {
            return $name;  
        }
    }

    /**
     * NOTE: Both the DEBUG_MODE, RETURN_BOOL, or NO_THROW_ON_ERROR will prevent
     *       a PDOException from being thrown
     */
    protected function _execStatement(string $sql, array $params = [], int $flags = 0): bool|int|array|object|null
    {
        $results = [];
        $this->LastInsertId = 0;
        $this->RowsAffected = 0;
        $this->LastExecException = null;

        if ($flags & self::DEBUG_MODE) {
            $flagsArr = [];
            if ($flags & self::DEBUG_MODE) $flagsArr[] = 'DEBUG_MODE';
            if ($flags & self::NO_THROW_ON_ERROR) $flagsArr[] = 'NO_THROW_ON_ERROR';
            if ($flags & self::USE_ROWSETS) $flagsArr[] = 'USE_ROWSETS';
            if ($flags & self::MERGED_ROWSETS) $flagsArr[] = 'MERGED_ROWSETS';
            if ($flags & self::RETURN_BOOL) $flagsArr[] = 'RETURN_BOOL';
            if ($flags & self::RETURN_ROWS_AFFECTED) $flagsArr[] = 'RETURN_ROWS_AFFECTED';
            if ($flags & self::RETURN_LAST_INSERT_ID) $flagsArr[] = 'RETURN_LAST_INSERT_ID';
            if ($flags & self::RETURN_FIRST_RESULT_ONLY) $flagsArr[] = 'RETURN_FIRST_RESULT_ONLY';
            if ($flags & self::INSERT_IGNORE) $flagsArr[] = 'INSERT_IGNORE';
            if ($flags & self::ON_DUPLICATE_UPDATE) $flagsArr[] = 'ON_DUPLICATE_UPDATE';
            if ($flags & self::DISABLE_PLACEHOLDERS) $flagsArr[] = 'DISABLE_PLACEHOLDERS';
            $this->DebugData = [
                'sql'           => $sql,
                'params'        => $params,
                'flags'         => implode(', ', $flagsArr),
            ];
        }

        if ($flags & self::DEBUG_MODE or $flags & self::RETURN_BOOL or $flags & self::NO_THROW_ON_ERROR) {
            try {
                $statement = $this->Handler->prepare($sql);
                $statement->execute($params);
            } catch(PDOException $e) {
                $this->LastExecException = $e;
                if ($flags & self::DEBUG_MODE) {
                    $this->DebugData['PDOException'] = [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ];
                }
                return false;
            }
        } else {
            $statement = $this->Handler->prepare($sql);
            $statement->execute($params);
        }
        $this->LastInsertId = intval($this->Handler->lastInsertId());
        $this->RowsAffected = intval($statement->rowCount());

        if ($flags & self::DEBUG_MODE) {
            $this->DebugData['lastInsertId'] = $this->getLastInsertId();
            $this->DebugData['RowsAffected'] = $this->getRowsAffected();
        }

        if($flags & self::RETURN_BOOL) {
            $results = true;
        } elseif($flags & self::RETURN_ROWS_AFFECTED) {
            $results = $this->getRowsAffected();
        } elseif($flags & self::RETURN_LAST_INSERT_ID) {
            $results = $this->getLastInsertId();
        } else {
            if (($flags & self::USE_ROWSETS) or ($flags & self::MERGED_ROWSETS)) {
                do {
                    if ($flags & self::MERGED_ROWSETS) {
                        $results = array_merge($results, $statement->fetchAll());
                    } else {
                        $results[] = $statement->fetchAll();
                    }
                } while ($statement->nextRowset());
            } else {
                $results = $statement->fetchAll();
            }
            if ($flags & self::RETURN_FIRST_RESULT_ONLY) {
                if (empty($results) === false) {
                    $results = $results[0];
                } else {
                    $results = null;
                }
            }
        }

        return $results;
    }

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
