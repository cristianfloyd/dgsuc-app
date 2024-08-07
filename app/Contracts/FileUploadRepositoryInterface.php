<?php

namespace App\Contracts;

interface FileUploadRepositoryInterface
{
    public function findOrFail($id);
    public function delete($model);
    /**
     * Create a new uploaded file record.
     *
     * @param array $data
     * @return \App\Models\UploadedFile
     */
    public function create(array $data);
    public function all();
}
