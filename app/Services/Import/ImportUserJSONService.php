<?php


namespace App\Services\Import;


use App\Exceptions\ImportException;
use App\Filters\AgeFilter;
use App\Models\Import;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportUserJSONService extends BaseImport
{

    protected array $filters = [
      AgeFilter::class
    ];

    public function getFile(Import $import) :array
    {
        if($import->storage_disk === $disk = Config::get('constants.import.storage.local'))
        {
            $file = Storage::disk($disk)->get($import->path);
            return json_decode($file, true);
        }
        elseif ($import->storage_disk ===  Config::get('constants.import.storage.url'))
        {
           return  Http::get($import->path)->json();
        }

        throw new ImportException("Storage disk location [{$import->storage_disk}] not supported");
    }

}