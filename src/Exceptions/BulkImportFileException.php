<?php

namespace Dyrynda\Artisan\Exceptions;

use Exception;
use RuntimeException;
use InvalidArgumentException;

class BulkImportFileException extends Exception
{
    public static function unsupported($type)
    {
        return new InvalidArgumentException(strtoupper($type) . " is unsupported at this time");
    }

    public static function noExtension()
    {
        return new InvalidArgumentException("Filename must contain an extension (Example: import.csv, import .json)");
    }

    public static function notExist($path)
    {
        return new InvalidArgumentException("{$path} does not exist");
    }

    public static function invalidSyntax($filename, $error = null)
    {
        return new RuntimeException("Errors detected in structure of {$filename}" . ($error ? ': ' . $error : ''));
    }

    public static function noFields()
    {
        return new RuntimeException("Could not get a list of fields from the file");
    }
}
