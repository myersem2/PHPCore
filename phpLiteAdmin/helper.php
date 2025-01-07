<?php declare(strict_types=1);
/**
 * PHPCore - phpLiteAdmin Helper
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

// Hide extended schema DB
$extended_schema_db = 'extended_schema.db';
$hide_extended_schema = true;

/**
 * phpLiteAdmin Helper Class
 *
 * This class is used to help phpLiteAdmin to work with the PHPCore and extended_schema.
 */
final class Helper
{

    // extended schema parameters
    const EXT_PARMS = [
      /* *
      "type" => [
        "type" => "enum",
        "options":[
          "null", "boolean", "object", "array", "number", "string"
        ]
      ],
      /* */
      "minimum"    => [ "type" => "number", "htmlAttr" => "min" ],
      "maximum"    => [ "type" => "number", "htmlAttr" => "max" ],
      "multipleOf" => [ "type" => "number", "htmlAttr" => "step" ],
    ];

    // Language index
    const LANG = [
      "minimum" => "Min",
      "maximum" => "Max",
      "multipleOf" => "Multiple Of",
    ];

    public static function drawHeaderColumns(): void
    {
      global $lang;
      foreach (self::EXT_PARMS as $key => $param) {
        echo "<td class='tdheader'>{$lang[$key]}</td>";
      }
    }

    public static function drawInputField(int $i, int $j, ?string $value, array $colParams = [], bool $edit = false): void
    {
      $id = "row_{$j}_field_{$i}_value";
      $name = ($edit) ? "{$i}[]" : "$j:$i";
      $type = 'text';
      $attributes = [];

      switch ($colParams['type']) {
        case 'INTEGER':
          if ( ! isset($colParams['multipleOf'])) {
            $colParams['multipleOf'] = 1;
          }
        // continue
        case 'REAL':
        case 'NUMERIC':
          $type = 'number';
          if ( ! isset($colParams['multipleOf'])) {
            $colParams['multipleOf'] = 'any';
          }
        break;
        case 'BLOB':      $type = 'file';           break;
        case 'DATE':      $type = 'date';           break;
        case 'DATETIME':  $type = 'datetime-local'; break;
        case 'TIME':      $type = 'time';           break;
        case 'BOOLEAN':
          $type = 'checkbox';
          $attributes['checked'] = ($value);
          $value = 1;
        break;
        case 'TEXT':
          
        break;
        default:
          return; /// TODO: build out
        // TODO: build
        //case 'NONE':
      }

      foreach (self::EXT_PARMS as $key => $param) {
        if (isset($param['htmlAttr'])) {
          $attributes[$param['htmlAttr']] = $colParams[$key] ?? null;
        }
      }

      echo "<input type='$type' id='$id' name='$name' value='$value'";
      foreach ($attributes as $attr => $attr_value) {
        echo isset($attr_value) ? " $attr='$attr_value'" : '';
      }
      echo " onblur='changeIgnore(this, \"row_{$j}_ignore\");'";
      echo " onclick='notNull(\"row_{$j}_field_{$i}_null\");'";
      echo " />";

      //echo "<textarea id='row_".$j."_field_".$i."_value' name='".$j.":".$i."' rows='5' cols='60' onclick='notNull(\"row_".$j."_field_".$i."_null\");' onblur='changeIgnore(this, \"row_".$j."_ignore\");'>".$value."</textarea>";
      //echo "<textarea id='row_".$j."_field_".$i."_value' name='".$i."[]' rows='1' cols='60' class='".htmlencode($field)."_textarea' onblur='changeIgnore(this, \"".$j."\", \"row_".$j."_field_".$i."_null\")'>".htmlencode($value)."</textarea>";

    }

    public static function drawColumns(int $i, array $colParams = []): void
    {
      foreach (self::EXT_PARMS as $key => $param) {
        echo "<td class='td" . ($i%2 ? "1" : "2") . "'>";
        if ( ! array_key_exists($key, $colParams) ) {
          echo '?';
        } elseif($colParams[$key] === null) {
          echo "<i class='null'>NULL</i>";
        } else {
          echo htmlencode($colParams[$key]);
        }
        echo "</td>";
      }
    }

    public static function drawColumnsForm(int $i, array $colParams = []): void
    {
      foreach (self::EXT_PARMS as $key => $param) {
        echo "<td class='td" . ($i%2 ? "1" : "2") . "'>";
        echo "<input type='{$param['type']}' name='{$i}_{$key}' style='width:100px;'";
        if (isset($colParams[$key])) {
          echo " value='{$colParams[$key]}'";
        }
        echo " /></td>";
      }
    }

    public static function getTableInfo(string $table, \Database $liteDb)
    {
      // Clean $database (remove .xxxx)
      list($database) = explode('.', $liteDb->getName());

      $info = $liteDb->selectArray("PRAGMA table_info({$liteDb->quote_id($table)})");
      $extSchema = database()->getTableSchema($table, $database);
      if ( ! empty($extSchema->columns)) {
        foreach ($extSchema->columns as &$extRow) {
          foreach ($info as &$infoRow) {
            if ($extRow->column == $infoRow['name']) {
              foreach (self::EXT_PARMS as $key => $param) {
                $infoRow[$key] = $extRow->$key ?? null;
              }
            }
          }
        }
      }

      return $info;
    }

    /**
     * Merge Lang
     *
     * @param array &$lang Language Array
     * @return void
     */
    public static function mergeLang( array &$lang ): void
    {
      foreach ( self::LANG as $key => $val ) {
        $lang[$key] = $val;
      }
    }

    /**
     * Post Extract Ext Params
     *
     * @return array of passed parameters
     */
    public static function postExtractExtParams(): array
    {
      static $data = [];
      if (empty($data)) {
        for($i=0;$i<=1000;$i++) {
          if ( ! isset($_POST["{$i}_field"])) break;
          $column = $_POST["{$i}_field"];
          foreach (self::EXT_PARMS as $key => $param ) {
            $data[$column][$key] = $param['default'] ?? null;
            if ( ! isset($_POST["{$i}_{$key}"])) continue;
            if (is_numeric($_POST["{$i}_{$key}"])) {
              $data[$column][$key] = floatval($_POST["{$i}_{$key}"]);
            }
            unset($_POST["{$i}_{$key}"]);
          }
        }
      }
      return $data;
    }

    /**
     * Delete Ext Schema Columns
     *
     * @param array Ext Schema Params
     */
    public static function deleteExtSchemaColumns(string $database, ?string $table = null, ?array $columns = null): bool
    {
      // Clean $database (remove .xxxx)
      list($database) = explode('.', $database);

      if (isset($table) and ! empty($columns)) {
        foreach ($columns as $column) {
          $params = [
            'database' => $database,
            'table'    => $table,
            'column'   => $column,
          ];
          $query = 'DELETE FROM `extended_schema`.`column_validation`'
          .' WHERE `database` = :database AND `table` = :table AND `column` = :column';
          database()->exec($query, $params);
        }
      } elseif (isset($table) and empty($columns)) {
        $params = [
          'database' => $database,
          'table'    => $table,
        ];
        $query = 'DELETE FROM `extended_schema`.`column_validation`'
        .' WHERE `database` = :database AND `table` = :table';
        database()->exec($query, $params);
      } else {
        $params = [
          'database' => $database,
        ];
        $query = 'DELETE FROM `extended_schema`.`column_validation`'
        .' WHERE `database` = :database';
        database()->exec($query, $params);
      }


      return true;
    }

    /**
     * Update Ext Schema Columns
     *
     * @param array Ext Schema Params
     */
    public static function updateExtSchemaColumns(string $database, string $table, array $extParams): bool
    {
      // Clean $database (remove .xxxx)
      list($database) = explode('.', $database);

      foreach ($extParams as $column => $extParam) {
        $keys = [
          'database' => $database,
          'table'    => $table,
          'column'   => $column,
        ];
        $query = 'INSERT OR IGNORE INTO `extended_schema`.`column_validation` '
        .'(`database`, `table`, `column`, `' . implode('`, `', array_keys(self::EXT_PARMS)) . '`) VALUES (:database, :table, :column'
        .', :' . implode(', :', array_keys(self::EXT_PARMS))
        .')';
        $params = $keys;
        foreach (array_keys(self::EXT_PARMS) as $key) {
          $params[$key] = $extParams[$key] ?? null;
        }
        database()->exec($query, $params);
        $params = $keys;
        $sets = [];
        foreach (array_keys(self::EXT_PARMS) as $key) {
          $sets[] = "`$key` = :$key";
          $params[$key] = $extParam[$key] ?? null;
        }
        $query = 'UPDATE `extended_schema`.`column_validation` SET ' . implode(', ', $sets)
        .' WHERE `database` = :database AND `table` = :table AND `column` = :column';
        database()->exec($query, $params);
      }
      return true;
    }

/*
    private static function extDatabase(): object
    {
      global $extended_schema_db, $extended_schema_path;
      static $ext_db;
      if ( ! isset($ext_db)) {
        $ext_db = new \Database([
          'path' => $extended_schema_path,
          'name' => $extended_schema_db,
          'writable' => is_writable($extended_schema_path),
          'writable_dir' => is_writable(dirname($extended_schema_path)),
          'readable' => is_readable($extended_schema_path),
        ]);
      }
      return $ext_db;
    }
*/
}

//	class MicroTimer (issue #146)
//	wraps calls to microtime(), calculating the elapsed time and rounding output
//
class MicroTimer {

	private $startTime, $stopTime;

	// creates and starts a timer
	function __construct()
	{
		$this->startTime = microtime(true);
	}

	// stops a timer
	public function stop()
	{
		$this->stopTime = microtime(true);
	}

	// returns the number of seconds from the timer's creation, or elapsed
	// between creation and call to ->stop()
	public function elapsed()
	{
		if ($this->stopTime)
			return round($this->stopTime - $this->startTime, 4);

		return round(microtime(true) - $this->startTime, 4);
	}

	// called when using a MicroTimer object as a string
	public function __toString()
	{
		return (string) $this->elapsed();
	}

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
