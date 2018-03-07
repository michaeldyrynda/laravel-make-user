<?php

namespace Dyrynda\Artisan\BulkImport;

use SplFileInfo;

interface BulkImportFileHandler
{
    public function __construct(SplFileInfo $filePath);

    /**
     * Get the info from the file.
     *
     * @return array
     */
    public function getData();
}
