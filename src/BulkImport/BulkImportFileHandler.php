<?php

namespace Dyrynda\Artisan\BulkImport;

interface BulkImportFileHandler
{
    public function __construct($filePath);

    /**
     * Retrieve data from file.
     *
     * @return array
     */
    public function getData();
}
