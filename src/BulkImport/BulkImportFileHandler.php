<?php

namespace Dyrynda\Artisan\BulkImport;

interface BulkImportFileHandler
{
	public function __construct($filePath);
	public function getData();
}
