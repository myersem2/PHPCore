<?php declare(strict_types=1);
/**
 * PHPCore - Request File
 *
 * @package   PHPCore
 * @author    Everett Myers <Everett@MyersNetwork.com>
 * @copyright 2022-2025 Everett Myers
 * @license   MIT License
 * @link      https://PHPCore.org
 * @version   2025-01-05
 */

namespace PHPCore;
use PHPCore\Exceptions\RequestException;

// -----------------------------------------------------------------------------

/**
 * Request File Class
 *
 * The RequestFile class is used internally for the Request class for the
 * ``getFile`` and `getFiles` method that represent an uploaded file of files
 * from a HTTP method request.
 */
final class RequestFile
{
    /**
     * Options flags
     * @option int
     */
    const EXCEPTION_ON_ERROR  = 1;
    const CHECK_INJECTION  = 2;
    const CHECK_TRUE_TYPE  = 4;

    // ---------------------------------------------------------------------

    /**
     * The original name of the file on the client machine.
     * @prop string
     */
    public string $name;

    /**
     * The mime type of the file, if the browser provided this information. An
     * example would be "image/gif". This mime type is however not checked on
     * the PHP side and therefore don't take its value for granted.
     * @prop string
     */
    public string $type;

    /**
     * The size, in bytes, of the uploaded file.
     * @prop int
     */
    public int $size;

    /**
     * The temporary filename of the file in which the uploaded file was stored
     * on the server.
     * @prop string
     */
    public string $tmp_name;

    /**
     * The error code associated with this file upload.
     * @prop int
     */
    public int $error;

    /**
     * The full path as submitted by the browser. This value does not always
     * contain a real directory structure, and cannot be trusted. Available as
     * of PHP 8.1.0.
     * @prop string
     */
    public string $full_path;

    /**
     * True Type
     * @prop ?string
     */
    public ?string $true_type;

    // ---------------------------------------------------------------------

    /**
     * Constructor
     *
     * Creates a RequestFile instance representing an uploaded file from a HTTP
     * POST method request.
     *
     * @param array $file An associative array of items uploaded to the current
     *                    script via the HTTP POST method.
     * @param int $flags Bitwise flags for this method
     * @return void
     */
    public function __construct(array $file, int $flags = 0)
    {
        foreach (['name','type','size','tmp_name','full_path'] as $key) {
            $this->$key = $file[$key];
        }

        if ($flags & self::CHECK_INJECTION && ! is_uploaded_file($this->tmp_name) && empty($file['error'])) {
            $this->error = RequestException::UPLOAD_ERR_INJECTION;
        } elseif ($flags & self::CHECK_TRUE_TYPE && ! $this->isTrueType()) {
            $this->error = RequestException::UPLOAD_ERR_WRONG_TYPE;
        } else {
            $this->error = $file['error'];
        }

        if ($flags & self::EXCEPTION_ON_ERROR && $this->error !== 0) {
            throw new RequestException(null, $this->error);
        }
    }

    /**
     * Get file contents
     *
     * This method will invoke ``file_get_contents()`` on the file using the
     * assigned ``tmp_name`` array variable to return the file contents as a
     * string.
     *
     * @note If there is no file or if there was an error uploading ``null``
     *       will be returned.
     *
     * @return ?string File contents as a string
     */
    public function getContents(): ?string
    {
        if (empty($this->tmp_name) || ! empty($this->error)) {
            return null;
        }
        return file_get_contents($this->tmp_name);
    }

    /**
     * Get error message
     *
     * This method is used to get the string for a given error code from an
     * upload.
     *
     * @return string Error message string
     */
    public function getErrorMessage(): string
    {
        return RequestException::ERRORS_DESCRIPTIONS[$this->error];
    }

    /**
     * Is true file true type
     *
     * This method uses PHP finfo class to determine the uploaded file's true
     * type.
     *
     * @see https://www.php.net/manual/en/class.finfo.php
     *
     * @return bool File matches true file type
     */
    public function isTrueType(): bool
    {
        static $finfo;
        if ( ! isset($finfo)) {
            $finfo = new \finfo(FILEINFO_MIME);
        }

        if ( ! empty($this->tmp_name)) {
            list($this->true_type) = explode(
                ';',
                $finfo->buffer(file_get_contents($this->tmp_name))
            );
        }

        return ($this->true_type === $this->type);
    }
}

// EOF /////////////////////////////////////////////////////////////////////////
