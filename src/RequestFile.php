<?php declare(strict_types=1);
/**
 * PHPCore - Request
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

namespace PHPCore;

// -------------------------------------------------------------------------------------------------

/**
 * Request File Class
 *
 * The RequestFile class is used internally for the Request class for the file
 * and files method.
 */
final class RequestFile
{
    /**
     * True Type
     * @var string
     */
    private $true_type = 'UNKNOWN';

    // ---------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct($file)
    {
        foreach (array_keys($file) as $key) {
            $this->$key = $file[$key];
        }

        if ( ! is_uploaded_file($this->tmp_name) && empty($this->error)) {
            $this->error = 5;
        }
    }

    /**
     * Get file contents
     *
     * This method will invoke file_get_contents() on the file using tmp_name
     * to return the file contents as a string.
     *
     * If there is no file or if there was an error uploading NULL will be
     * returned.
     *
     * @return string File contents as a string
     */
    public function getContents(): string|null
    {
        if (empty($this->tmp_name) || ! empty($this->error)) {
            return null;
        }
        return file_get_contents($this->tmp_name);
    }

    /**
     * Get upload error
     *
     * This method will invoke file_get_contents() on the file using tmp_name
     * to return the file contents as a string.
     *
     * If there is no error NULL will be returned.
     *
     * @return string Error message
     */
    public function getError(): string|null
    {
        switch($this->error) {
          case UPLOAD_ERR_OK:
                return null;
          break;
          case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
          break;
          case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
          break;
          case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
          break;
          case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
          break;
          case 5:
                return 'File was not uploaded via HTTP POST';
          break;
          case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
          break;
          case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
          break;
          case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
          break;
          default:
                return 'There was a problem with your upload.';
          break;
        }
    }

    /**
     * File true type
     *
     * This method uses PHP finfo class to determine the uploaded file's true
     * type.
     *
     * @see https://www.php.net/manual/en/class.finfo.php
     *
     * @return string Error message
     */
    public function trueType(): string
    {
        static $finfo;

        if ( ! isset($finfo)) {
            $finfo = new \finfo(FILEINFO_MIME);
        }

        if (empty($this->true_type) && ! empty($this->tmp_name)) {
            list($this->true_type) = explode(';', $finfo->buffer(file_get_contents($this->tmp_name)));
        }

        return $this->true_type;
    }
}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////
