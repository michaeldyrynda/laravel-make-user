<?php

namespace Dyrynda\Artisan\Exceptions;

use Exception;

class ImportFileException extends Exception
{
    /**
     * File type is unsupported.
     *
     * @param  string  $type
     * @return \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public static function unsupported($type)
    {
        return new static(strtoupper($type) . " is unsupported at this time");
    }

    /**
     * File has no extension
     *
     * @return \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public static function noExtension()
    {
        return new static("Filename must contain an extension (Example: import.csv, import .json)");
    }

    /**
     * File doesn't exist
     *
     * @param  string  $path
     * @return \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public static function notExist($path)
    {
        return new static("{$path} does not exist");
    }

    /**
     * File syntax is invalid
     *
     * @param  string  $filename
     * @param  string|null  $error
     * @return \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public static function invalidSyntax($filename, $error = null)
    {
        return new static("Errors detected in structure of {$filename}" . ($error ? ': ' . $error : ''));
    }


    /**
     * Unable to get the list of fields/columns from file
     *
     * @return \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public static function noFields()
    {
        return new static("Could not get a list of fields from the file");
    }
}
