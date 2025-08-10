<?php

namespace App\Livewire;

use App\Models\UploadedFile;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class FileEncoding extends Component
{
    use MapucheConnectionTrait;

    public $files;

    public $selectedFile;

    public $fileEncoding;

    public $systemEncoding;

    public $databaseEncoding;

    public $inputString;

    public $convertedString;

    public $characterCount;

    public $convertedFilePath;

    /**
     * Summary of updatedSelectedFile.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function updatedSelectedFile($value): void
    {
        $file = UploadedFile::query()->findOrFail($value);
        $this->fileEncoding = $this->detectEncoding(Storage::path("/public/{$file->file_path}"));
    }

    public function getDataBaseEncoding(): void
    {
        $result = DB::connection($this->getConnectionName())
            ->select('SHOW server_encoding');
        $this->databaseEncoding = $result[0]->server_encoding;
    }

    public function convertToDatabaseEncoding(): void
    {
        // $this->convertedString = mb_convert_encoding($this->inputString, $this->databaseEncoding);
        $this->convertedString = mb_convert_encoding($this->convertedString, $this->systemEncoding);
        $this->countCharacters($this->convertedString);
    }

    public function countCharacters($string): void
    {
        $this->characterCount = mb_strlen($string);
    }

    public function mount(): void
    {
        $this->files = UploadedFile::all();
        $this->systemEncoding = mb_internal_encoding();
        $this->getDatabaseEncoding();
    }

    public function convertFileToUtf8(): void
    {
        $filePath = $this->uploadedFile->getRealPath();
        $fileContent = file_get_contents($filePath);
        $encoding = mb_detect_encoding($fileContent, mb_list_encodings(), true);

        if ($encoding !== 'UTF-8') {
            $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', $encoding);
            $newFileName = 'converted_' . $this->uploadedFile->getClientOriginalName();
            $this->convertedFilePath = Storage::put("converted_files/$newFileName", $utf8Content);
        } else {
            $this->convertedFilePath = $filePath;
        }
    }

    public function render()
    {
        return view('livewire.file-encoding');
    }

    /**
     * @param mixed $filePath
     *
     * @return bool|string
     */
    private function detectEncoding($filePath): bool|string
    {
        $fileContent = file_get_contents($filePath);
        $encoding = mb_detect_encoding($fileContent, mb_list_encodings(), true);

        return $encoding ?? 'No se pudo detectar la codificación';
    }
}
