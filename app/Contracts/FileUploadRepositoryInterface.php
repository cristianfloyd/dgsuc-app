<?php

namespace App\Contracts;

interface FileUploadRepositoryInterface
{
    public function findOrFail($id);
    public function delete($model);
    public function all();
}
