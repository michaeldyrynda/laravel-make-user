<?php

namespace Dyrynda\Artisan\BulkImport\Handlers;

use SplFileInfo;
use Dyrynda\Artisan\Exceptions\BulkImportFileException;

abstract class Base 
{
	protected $file;
	protected $fields;
	protected $filePath;
	protected $fileHandle;

	public function __construct($filePath)
	{
        $this->filePath = $filePath;

        $this->file = new SplFileInfo($filePath);

        if (! $this->file->getExtension()) {
            throw BulkImportFileException::noExtension();
        }
        
        if (! $this->file->isFile()) {
            throw BulkImportFileException::notExist($filePath);
        }
        
		$this->fileHandle = $this->file->openFile();

        $this->validateSyntax();
	}
}
