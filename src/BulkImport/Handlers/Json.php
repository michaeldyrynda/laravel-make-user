<?php

namespace Dyrynda\Artisan\BulkImport\Handlers;

use Dyrynda\Artisan\Exceptions\ImportFileException;
use Dyrynda\Artisan\BulkImport\BulkImportFileHandler;

class Json extends Base implements BulkImportFileHandler
{
    public function getData()
    {
        return json_decode(file_get_contents($this->filePath), true, 512, JSON_BIGINT_AS_STRING);
    }

    protected function validateSyntax()
    {
        $data = json_decode(file_get_contents($this->filePath), true);

        if (json_last_error()) {
            throw ImportFileException::invalidSyntax($this->file->getFilename());
        }
        
        $fields = array_keys($data[0]);

        foreach ($data as $row) {
           if (count($fields) != count($row) || count(array_intersect($fields, array_keys($row))) != count($fields)) {
                throw ImportFileException::invalidSyntax($this->file->getFilename(), 'Fields not consistent');
           }
        }
    }

    /**
     * Get list of columns from the file.
     *
     * @return array
     */
    protected function getFields()
    {
        $data = json_decode(file_get_contents($this->filePath), true);

        return isset($data[0]) ? array_map('strtolower', array_map('trim', array_keys($data[0]))) : [];
    }
}
