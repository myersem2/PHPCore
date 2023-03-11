<?php declare(strict_types=1);
/**
 * PHPCore - Model
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore\Interface;

// -------------------------------------------------------------------------------------------------

/**
 * Model Interface
 */
interface Model
{
    public function __construct(array|object $data = null);
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
