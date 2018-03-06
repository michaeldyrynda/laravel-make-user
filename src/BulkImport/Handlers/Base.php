<?php

namespace Dyrynda\Artisan\BulkImport\Handlers;

use SplFileInfo;
use Dyrynda\Artisan\Exceptions\ImportFileException;

abstract class Base
{
    protected $file;
    protected $fields;
    protected $filePath;
    protected $fileHandle;

    /**
     * Base constructor.
     * @param $filePath
     *
     * @throws \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->file = new SplFileInfo($filePath);

        if (! $this->file->getExtension()) {
            throw ImportFileException::noExtension();
        }

        if (! $this->file->isFile()) {
            throw ImportFileException::notExist($filePath);
        }

        $this->fileHandle = $this->file->openFile();

        $this->validateSyntax();
    }

    /**
     * Checks file for valid syntax.
     *
     * @throws \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    abstract protected function validateSyntax();
}
