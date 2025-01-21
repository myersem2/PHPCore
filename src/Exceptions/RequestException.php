<?php declare(strict_types=1);
/**
 * PHPCore - RequestException
 *
 * @package   PHPCore
 * @category  Exception
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-12
 */

namespace PHPCore\Exceptions;

// -----------------------------------------------------------------------------

/**
 * Request Exception Class
 *
 * The Exception class is used to throw exceptions in the Request class.
 *
 * @codeCoverageIgnore
 */
final class RequestException extends \Exception
{
    /**
     * Upload error codes
     * @const int
     */
    public const UPLOAD_ERR_OK = UPLOAD_ERR_OK;
    public const UPLOAD_ERR_INI_SIZE = UPLOAD_ERR_INI_SIZE;
    public const UPLOAD_ERR_FORM_SIZE = UPLOAD_ERR_FORM_SIZE;
    public const UPLOAD_ERR_PARTIAL = UPLOAD_ERR_PARTIAL;
    public const UPLOAD_ERR_NO_FILE = UPLOAD_ERR_NO_FILE;
    public const UPLOAD_ERR_E = 5; // No longer used
    public const UPLOAD_ERR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    public const UPLOAD_ERR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    public const UPLOAD_ERR_EXTENSION = UPLOAD_ERR_EXTENSION;
    public const UPLOAD_ERR_INJECTION = 9;
    public const UPLOAD_ERR_WRONG_TYPE = 10;
    public const CONFIG_ERR_IP_SVR_PARM = 11;

    /**
     * Error descriptions
     *
     * @ignore
     * @const array
     */
    public const ERRORS_DESCRIPTIONS = [
        // ----- PHP standard UPLOAD error codes -----
        // UPLOAD_ERR_OK
        0 => 'There is no error, the file uploaded with success.',
        // UPLOAD_ERR_INI_SIZE
        1 => 'Size exceeds upload_max_filesize in php.ini.',
        // UPLOAD_ERR_FORM_SIZE
        2 => 'Size exceeds MAX_FILE_SIZE specified in HTML form.',
        // UPLOAD_ERR_PARTIAL
        3 => 'The uploaded file was only partially uploaded.',
        // UPLOAD_ERR_NO_FILE
        4 => 'No file was uploaded.',
        // UPLOAD_ERR_E
        5 => '', // No longer used
        // UPLOAD_ERR_NO_TMP_DIR
        6 => 'Missing a temporary folder.',
        // UPLOAD_ERR_CANT_WRITE
        7 => 'Failed to write file to disk.',
        // UPLOAD_ERR_EXTENSION
        8 => 'A PHP extension stopped the file upload.',

        // ----- PHPCore UPLOAD codes -----
        // UPLOAD_ERR_INJECTION
        9 => 'File was not uploaded via HTTP POST',
        // UPLOAD_ERR_WRONG_TYPE
        10 => 'File true type did not match the uploaded type.',
        // CONFIG_ERR_IP_SVR_PARM
        11 => 'Empty `request.ip_server_params` in phpcore.ini.',
    ];

    // ---------------------------------------------------------------------

    /**
     * Contruct class
     *
     * This method is used to construct a new new `ReuestEx
     *
     * @return ?string Exception message
     * @return int Exception code
     * @return ?Throwable Previous exception if nested exception
     * @return void
     */
    public function __construct(
        ?string $message,
        int $code = 0,
        Throwable $previous = null
    ) {
        if ( ! isset(self::ERRORS_DESCRIPTIONS[$code])) {
            throw new \Exception(
                "Invalid RequestException code $code" 
            );
        }

        if ($message === null) {
            $message = self::ERRORS_DESCRIPTIONS[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * To string
     *
     * This method is get the description of the exception.
     *
     * @return string Exception description
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
