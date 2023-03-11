<?php declare(strict_types=1);
/**
 * PHPCore - Model
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Model trait
 */
trait Model
{
    /**
     * The table name for this model
     * The following for reference only and is required to be set in the class
     * that is using this traits.
     */
    // const TABLE_NAME = 'table';

    /**
     * The primary key name
     * The following for reference only and is required to be set in the class
     * that is using this traits.
     */
    // const PRIMARY_KEY = 'id';

    // -----------------------------------------------------------------------------------------

    /**
     * Get Record By Primary Key
     */
    public static function getRecord(mixed $primaryKey): static|null
    {
        $record = database()->getRecord(self::TABLE_NAME, [
          self::PRIMARY_KEY => $primaryKey
        ]);
        if (empty($record)) return null;
        return new static($record);
    }

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
