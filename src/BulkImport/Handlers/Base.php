<?php

namespace Dyrynda\Artisan\BulkImport\Handlers;

use SplFileInfo;
use Dyrynda\Artisan\Exceptions\ImportFileException;
use Dyrynda\Artisan\BulkImport\BulkImportFileHandler;

abstract class Base implements BulkImportFileHandler
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
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;

        $this->filePath = $file->getPathname();

        if (! $this->file->getExtension()) {
            throw ImportFileException::noExtension();
        }

        if (! $this->file->isFile()) {
            throw ImportFileException::notExist($this->filePath);
        }

        $this->fileHandle = $this->file->openFile();

        $this->validateSyntax();
    }

    /**
     * Checks file for valid syntax.
     *
     * @return void
     * @throws \Dyrynda\Artisan\Exceptions\ImportFileException
     */
    abstract protected function validateSyntax();

    /**
     * Get the info from the file.
     *
     * @return array
     */
    abstract public function getData();
}
