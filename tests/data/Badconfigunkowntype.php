<?php declare(strict_types=1);
/**
 * PHPCore - Badconfigunkowntype (FOR TESTING ONLY)
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-05
 */

namespace PHPCore;

// -----------------------------------------------------------------------------

/**
 * Bad Config Unkown Type Class
 *
 * This is a class for testing exceptions for the Config Class.
 */
final class Badconfigunkowntype
{
    /**
     * PHPCore ini config options.
     *
     * @const array
     */
    public const CONFIG_OPTIONS = [
        'badconfigunkowntype.string' => [
            'type'      => 'string',
            'default'   => '',
        ],
        'badconfigunkowntype.unknown_type' => [
            'type'      => 'unknown_type',
            'default'   => 'TEXT',
        ],
    ];
}

// EOF /////////////////////////////////////////////////////////////////////////
